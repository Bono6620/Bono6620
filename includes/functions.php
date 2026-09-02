<?php
require_once __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(400);
        exit('طلب غير صالح، حاول تاني.');
    }
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function get_member(int $id): ?array
{
    $stmt = get_db()->prepare('SELECT * FROM members WHERE id = ?');
    $stmt->execute([$id]);
    $member = $stmt->fetch();
    return $member ?: null;
}

function get_all_members(): array
{
    return get_db()->query('SELECT * FROM members ORDER BY first_name')->fetchAll();
}

function get_children(int $parentId): array
{
    $stmt = get_db()->prepare(
        'SELECT * FROM members WHERE father_id = :id1
         OR (father_id IS NULL AND mother_id = :id2)
         ORDER BY birth_year IS NULL, birth_year'
    );
    $stmt->execute(['id1' => $parentId, 'id2' => $parentId]);
    return $stmt->fetchAll();
}

function get_root_members(): array
{
    $stmt = get_db()->query(
        'SELECT * FROM members WHERE father_id IS NULL AND mother_id IS NULL ORDER BY birth_year IS NULL, birth_year'
    );
    $candidates = $stmt->fetchAll();

    $roots = [];
    foreach ($candidates as $member) {
        if (!empty($member['spouse_id'])) {
            $spouse = get_member((int) $member['spouse_id']);
            if ($spouse) {
                // الزوج/ة اللي ليه أب أو أم مسجلين هيظهر أصلاً جنب شريكه في الشجرة، فمنعرضوش هنا تاني.
                if (!empty($spouse['father_id']) || !empty($spouse['mother_id'])) {
                    continue;
                }
                // زوجين مالهمش أب ولا أم (عائلة مؤسسة) - بنعرض واحد منهم بس كجذر، والتاني بيظهر جنبه كزوج.
                if ((int) $spouse['id'] < (int) $member['id']) {
                    continue;
                }
            }
        }
        $roots[] = $member;
    }

    return $roots;
}

function member_photo_url(array $member): string
{
    if (!empty($member['photo'])) {
        return UPLOAD_URL . '/' . rawurlencode($member['photo']);
    }
    return $member['gender'] === 'female' ? 'assets/img/avatar-female.svg' : 'assets/img/avatar-male.svg';
}

function member_full_name(array $member): string
{
    return trim($member['first_name'] . ' ' . ($member['last_name'] ?? ''));
}

function member_years_label(array $member): string
{
    $birth = $member['birth_year'] ?: '؟';
    if (!empty($member['death_year'])) {
        return $birth . ' - ' . $member['death_year'];
    }
    return $member['birth_year'] ? (string) $birth : '';
}

function render_member_card(array $member): string
{
    $name = e(member_full_name($member));
    $years = e(member_years_label($member));
    $photo = e(member_photo_url($member));
    $genderClass = $member['gender'] === 'female' ? 'is-female' : 'is-male';

    $spouseHtml = '';
    if (!empty($member['spouse_id'])) {
        $spouse = get_member((int) $member['spouse_id']);
        if ($spouse) {
            $spouseHtml = '<div class="spouse-link">'
                . '<span class="spouse-icon">&#128141;</span>'
                . '<a href="member.php?id=' . (int) $spouse['id'] . '">' . e(member_full_name($spouse)) . '</a>'
                . '</div>';
        }
    }

    return '<div class="person-card ' . $genderClass . '">'
        . '<a href="member.php?id=' . (int) $member['id'] . '">'
        . '<img src="' . $photo . '" alt="' . $name . '" loading="lazy">'
        . '<span class="person-name">' . $name . '</span>'
        . ($years ? '<span class="person-years">' . $years . '</span>' : '')
        . '</a>'
        . $spouseHtml
        . '</div>';
}

function render_tree_node(array $member): string
{
    $html = '<li data-name="' . e(member_full_name($member)) . '">';
    $html .= render_member_card($member);

    $children = get_children((int) $member['id']);
    if ($children) {
        $html .= '<ul>';
        foreach ($children as $child) {
            $html .= render_tree_node($child);
        }
        $html .= '</ul>';
    }

    $html .= '</li>';
    return $html;
}

function handle_photo_upload(string $fieldName, ?string $existingPhoto = null): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $existingPhoto;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('حصل خطأ أثناء رفع الصورة.');
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('حجم الصورة أكبر من المسموح (2 ميجا).');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('نوع الصورة غير مدعوم، استخدم jpg أو png أو webp.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = UPLOAD_DIR . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('تعذر حفظ الصورة على السيرفر.');
    }

    if ($existingPhoto) {
        $oldPath = UPLOAD_DIR . '/' . $existingPhoto;
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    return $filename;
}
