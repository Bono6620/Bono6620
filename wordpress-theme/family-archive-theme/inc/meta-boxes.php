<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('add_meta_boxes', 'fa_add_meta_boxes');

function fa_add_meta_boxes(): void
{
    add_meta_box('fa_member_meta', 'بيانات الفرد', 'fa_render_member_meta_box', 'fa_member', 'normal', 'high');
    add_meta_box('fa_event_meta', 'بيانات الحدث', 'fa_render_event_meta_box', 'fa_event', 'normal', 'high');
    add_meta_box('fa_document_meta', 'بيانات الوثيقة', 'fa_render_document_meta_box', 'fa_document', 'normal', 'high');
    add_meta_box('fa_file_meta', 'الملف المرفق', 'fa_render_file_meta_box', 'fa_file', 'normal', 'high');
}

function fa_dropdown_members(string $name, int $selected, int $excludeId, ?string $genderFilter = null): void
{
    echo '<select name="' . esc_attr($name) . '" style="width:100%">';
    echo '<option value="0">-- بدون --</option>';
    foreach (fa_get_all_members() as $member) {
        if ($member->ID === $excludeId) {
            continue;
        }
        if ($genderFilter !== null) {
            $meta = fa_member_meta($member->ID);
            if ($meta['gender'] !== $genderFilter) {
                continue;
            }
        }
        printf(
            '<option value="%d" %s>%s</option>',
            $member->ID,
            selected($selected, $member->ID, false),
            esc_html(fa_member_full_name($member))
        );
    }
    echo '</select>';
}

function fa_render_member_meta_box(WP_Post $post): void
{
    wp_nonce_field('fa_save_member', 'fa_member_nonce');
    $meta = fa_member_meta($post->ID);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="fa_gender">النوع</label></th>
            <td>
                <select name="fa_gender" id="fa_gender">
                    <option value="male" <?php selected($meta['gender'], 'male'); ?>>ذكر</option>
                    <option value="female" <?php selected($meta['gender'], 'female'); ?>>أنثى</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="fa_birth_year">سنة الميلاد</label></th>
            <td><input type="number" name="fa_birth_year" id="fa_birth_year" value="<?php echo esc_attr((string) $meta['birth_year']); ?>" min="1500" max="2100"></td>
        </tr>
        <tr>
            <th><label for="fa_death_year">سنة الوفاة (لو متوفى)</label></th>
            <td><input type="number" name="fa_death_year" id="fa_death_year" value="<?php echo esc_attr((string) $meta['death_year']); ?>" min="1500" max="2100"></td>
        </tr>
        <tr>
            <th><label for="fa_father_id">الأب</label></th>
            <td><?php fa_dropdown_members('fa_father_id', (int) $meta['father_id'], $post->ID, 'male'); ?></td>
        </tr>
        <tr>
            <th><label for="fa_mother_id">الأم</label></th>
            <td><?php fa_dropdown_members('fa_mother_id', (int) $meta['mother_id'], $post->ID, 'female'); ?></td>
        </tr>
        <tr>
            <th><label for="fa_spouse_id">الزوج/الزوجة</label></th>
            <td><?php fa_dropdown_members('fa_spouse_id', (int) $meta['spouse_id'], $post->ID); ?></td>
        </tr>
    </table>
    <p class="description">اكتب نبذة أو سيرة عن الفرد في المحرر فوق. لو عايز صورة شخصية، استخدم "الصورة البارزة" على اليمين.</p>
    <?php
}

function fa_render_event_meta_box(WP_Post $post): void
{
    wp_nonce_field('fa_save_event', 'fa_event_nonce');
    $dateLabel = get_post_meta($post->ID, '_fa_date_label', true);
    $sortYear = get_post_meta($post->ID, '_fa_sort_year', true);
    $relatedId = (int) get_post_meta($post->ID, '_fa_related_member_id', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="fa_date_label">التاريخ (بأي صيغة)</label></th>
            <td><input type="text" name="fa_date_label" id="fa_date_label" value="<?php echo esc_attr($dateLabel); ?>" placeholder="مثال: 1970 أو 3 مارس 1970" style="width:100%"></td>
        </tr>
        <tr>
            <th><label for="fa_sort_year">السنة (لترتيب الأحداث فقط)</label></th>
            <td><input type="number" name="fa_sort_year" id="fa_sort_year" value="<?php echo esc_attr($sortYear); ?>" min="1" max="2100"></td>
        </tr>
        <tr>
            <th><label for="fa_related_member_id">مرتبط بفرد (اختياري)</label></th>
            <td><?php fa_dropdown_members('fa_related_member_id', $relatedId, 0); ?></td>
        </tr>
    </table>
    <p class="description">اكتب وصف الحدث في المحرر فوق.</p>
    <?php
}

function fa_render_document_meta_box(WP_Post $post): void
{
    wp_nonce_field('fa_save_document', 'fa_document_nonce');
    $category = get_post_meta($post->ID, '_fa_category', true) ?: 'other';
    $dateLabel = get_post_meta($post->ID, '_fa_date_label', true);
    $sortYear = get_post_meta($post->ID, '_fa_sort_year', true);
    $relatedId = (int) get_post_meta($post->ID, '_fa_related_member_id', true);
    $originalFile = get_post_meta($post->ID, '_fa_file_original', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="fa_category">النوع</label></th>
            <td>
                <select name="fa_category" id="fa_category">
                    <option value="waqf" <?php selected($category, 'waqf'); ?>>وقف</option>
                    <option value="inheritance" <?php selected($category, 'inheritance'); ?>>ورث</option>
                    <option value="other" <?php selected($category, 'other'); ?>>أخرى</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="fa_date_label">التاريخ (بأي صيغة)</label></th>
            <td><input type="text" name="fa_date_label" id="fa_date_label" value="<?php echo esc_attr($dateLabel); ?>" placeholder="مثال: 1962 أو 1390 هـ" style="width:100%"></td>
        </tr>
        <tr>
            <th><label for="fa_sort_year">السنة (للترتيب فقط)</label></th>
            <td><input type="number" name="fa_sort_year" id="fa_sort_year" value="<?php echo esc_attr($sortYear); ?>" min="1" max="2100"></td>
        </tr>
        <tr>
            <th><label for="fa_related_member_id">مرتبطة بفرد (اختياري)</label></th>
            <td><?php fa_dropdown_members('fa_related_member_id', $relatedId, 0); ?></td>
        </tr>
        <tr>
            <th><label for="fa_upload_file">صورة أو PDF للوثيقة</label></th>
            <td>
                <input type="file" name="fa_upload_file" id="fa_upload_file" accept=".jpg,.jpeg,.png,.webp,.pdf">
                <?php if ($originalFile): ?>
                    <p class="description">الملف الحالي: <?php echo esc_html($originalFile); ?> (ارفع ملف جديد لو عايز تستبدله)</p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <p class="description">اكتب وصف الوثيقة (بتتكلم عن ايه) في المحرر فوق.</p>
    <?php
}

function fa_render_file_meta_box(WP_Post $post): void
{
    wp_nonce_field('fa_save_file', 'fa_file_nonce');
    $originalFile = get_post_meta($post->ID, '_fa_file_original', true);
    $size = (int) get_post_meta($post->ID, '_fa_file_size', true);
    ?>
    <p>
        <label for="fa_upload_file">اختار الملف</label><br>
        <input type="file" name="fa_upload_file" id="fa_upload_file" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt">
    </p>
    <?php if ($originalFile): ?>
        <p class="description">الملف الحالي: <?php echo esc_html($originalFile); ?> (<?php echo esc_html(fa_format_bytes($size)); ?>) - ارفع ملف جديد لو عايز تستبدله.</p>
    <?php endif; ?>
    <p class="description">اكتب وصف الملف في المحرر فوق.</p>
    <?php
}

add_action('save_post_fa_member', 'fa_save_member_meta', 10, 2);

function fa_save_member_meta(int $postId, WP_Post $post): void
{
    if (!isset($_POST['fa_member_nonce']) || !wp_verify_nonce($_POST['fa_member_nonce'], 'fa_save_member')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_fa_member', $postId)) {
        return;
    }

    update_post_meta($postId, '_fa_gender', $_POST['fa_gender'] === 'female' ? 'female' : 'male');
    update_post_meta($postId, '_fa_birth_year', absint($_POST['fa_birth_year'] ?? 0) ?: '');
    update_post_meta($postId, '_fa_death_year', absint($_POST['fa_death_year'] ?? 0) ?: '');

    foreach (['fa_father_id' => '_fa_father_id', 'fa_mother_id' => '_fa_mother_id', 'fa_spouse_id' => '_fa_spouse_id'] as $field => $metaKey) {
        $value = absint($_POST[$field] ?? 0);
        if ($value === $postId) {
            $value = 0;
        }
        update_post_meta($postId, $metaKey, $value ?: '');
    }

    $spouseId = absint($_POST['fa_spouse_id'] ?? 0);
    if ($spouseId && $spouseId !== $postId) {
        update_post_meta($spouseId, '_fa_spouse_id', $postId);
    }
}

add_action('save_post_fa_event', 'fa_save_event_meta', 10, 2);

function fa_save_event_meta(int $postId, WP_Post $post): void
{
    if (!isset($_POST['fa_event_nonce']) || !wp_verify_nonce($_POST['fa_event_nonce'], 'fa_save_event')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_fa_event', $postId)) {
        return;
    }

    update_post_meta($postId, '_fa_date_label', sanitize_text_field($_POST['fa_date_label'] ?? ''));
    update_post_meta($postId, '_fa_sort_year', absint($_POST['fa_sort_year'] ?? 0) ?: '');
    update_post_meta($postId, '_fa_related_member_id', absint($_POST['fa_related_member_id'] ?? 0) ?: '');
}

add_action('save_post_fa_document', 'fa_save_document_meta', 10, 2);

function fa_save_document_meta(int $postId, WP_Post $post): void
{
    if (!isset($_POST['fa_document_nonce']) || !wp_verify_nonce($_POST['fa_document_nonce'], 'fa_save_document')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_fa_document', $postId)) {
        return;
    }

    $category = in_array($_POST['fa_category'] ?? '', ['waqf', 'inheritance', 'other'], true) ? $_POST['fa_category'] : 'other';
    update_post_meta($postId, '_fa_category', $category);
    update_post_meta($postId, '_fa_date_label', sanitize_text_field($_POST['fa_date_label'] ?? ''));
    update_post_meta($postId, '_fa_sort_year', absint($_POST['fa_sort_year'] ?? 0) ?: '');
    update_post_meta($postId, '_fa_related_member_id', absint($_POST['fa_related_member_id'] ?? 0) ?: '');

    fa_handle_secure_upload($postId, 'documents', FA_DOCUMENT_MIME_MAP);
}

add_action('save_post_fa_file', 'fa_save_file_meta', 10, 2);

function fa_save_file_meta(int $postId, WP_Post $post): void
{
    if (!isset($_POST['fa_file_nonce']) || !wp_verify_nonce($_POST['fa_file_nonce'], 'fa_save_file')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_fa_file', $postId)) {
        return;
    }

    fa_handle_secure_upload($postId, 'files', FA_FILE_MIME_MAP);
}

add_action('before_delete_post', 'fa_delete_attached_upload');

function fa_delete_attached_upload(int $postId): void
{
    $type = get_post_type($postId);
    if ($type === 'fa_document') {
        fa_delete_secure_upload($postId, 'documents');
    } elseif ($type === 'fa_file') {
        fa_delete_secure_upload($postId, 'files');
    }
}
