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

function handle_upload(string $fieldName, string $destDir, array $allowedMimes, int $maxBytes, ?string $existingFilename = null): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $existingFilename;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('حصل خطأ أثناء رفع الملف.');
    }
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('حجم الملف أكبر من المسموح.');
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowedMimes[$mime])) {
        throw new RuntimeException('نوع الملف غير مدعوم.');
    }

    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedMimes[$mime];
    $destination = $destDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('تعذر حفظ الملف على السيرفر.');
    }

    if ($existingFilename) {
        $oldPath = $destDir . '/' . $existingFilename;
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    return $filename;
}

const IMAGE_MIME_MAP = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

const DOCUMENT_MIME_MAP = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
];

const GENERIC_FILE_MIME_MAP = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'application/zip' => 'zip',
    'text/plain' => 'txt',
];

function handle_photo_upload(string $fieldName, ?string $existingPhoto = null): ?string
{
    return handle_upload($fieldName, UPLOAD_DIR, IMAGE_MIME_MAP, MAX_UPLOAD_BYTES, $existingPhoto);
}

function handle_document_upload(string $fieldName, ?string $existingFile = null): ?string
{
    return handle_upload($fieldName, UPLOAD_DIR . '/documents', DOCUMENT_MIME_MAP, MAX_DOCUMENT_BYTES, $existingFile);
}

function handle_generic_file_upload(string $fieldName, ?string $existingFile = null): ?string
{
    return handle_upload($fieldName, UPLOAD_DIR . '/files', GENERIC_FILE_MIME_MAP, MAX_DOCUMENT_BYTES, $existingFile);
}

function delete_uploaded_file(string $destDir, ?string $filename): void
{
    if (!$filename) {
        return;
    }
    $path = $destDir . '/' . $filename;
    if (is_file($path)) {
        @unlink($path);
    }
}

function format_bytes(int $bytes): string
{
    if ($bytes >= 1024 * 1024) {
        return round($bytes / (1024 * 1024), 1) . ' ميجا';
    }
    return round($bytes / 1024, 1) . ' كيلو';
}

function get_event(int $id): ?array
{
    $stmt = get_db()->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function get_all_events(): array
{
    return get_db()->query(
        'SELECT * FROM events ORDER BY sort_year IS NULL, sort_year, created_at'
    )->fetchAll();
}

function get_document(int $id): ?array
{
    $stmt = get_db()->prepare('SELECT * FROM documents WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function get_all_documents(): array
{
    return get_db()->query(
        'SELECT * FROM documents ORDER BY sort_year IS NULL, sort_year, created_at'
    )->fetchAll();
}

function document_category_label(string $category): string
{
    return match ($category) {
        'waqf' => 'وقف',
        'inheritance' => 'ورث',
        default => 'أخرى',
    };
}

function get_file_record(int $id): ?array
{
    $stmt = get_db()->prepare('SELECT * FROM files WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function get_all_files(): array
{
    return get_db()->query('SELECT * FROM files ORDER BY uploaded_at DESC')->fetchAll();
}
