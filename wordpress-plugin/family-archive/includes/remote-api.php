<?php
if (!defined('ABSPATH')) {
    exit;
}

function fa_get_remote_token(): string
{
    $token = get_option('fa_remote_token');
    if (!$token) {
        $token = fa_generate_remote_token();
    }
    return $token;
}

function fa_generate_remote_token(): string
{
    $token = bin2hex(random_bytes(32));
    update_option('fa_remote_token', $token, false);
    return $token;
}

add_action('admin_menu', 'fa_register_remote_settings_page');

function fa_register_remote_settings_page(): void
{
    add_submenu_page(
        'edit.php?post_type=fa_member',
        'التحكم عن بعد',
        'التحكم عن بعد',
        'manage_options',
        'fa-remote-api',
        'fa_render_remote_settings_page'
    );
}

function fa_render_remote_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['fa_regenerate_token']) && check_admin_referer('fa_remote_settings')) {
        fa_generate_remote_token();
        echo '<div class="notice notice-success"><p>تم إنشاء مفتاح جديد. المفتاح القديم بقى مش شغال.</p></div>';
    }

    $token = fa_get_remote_token();
    $endpoint = rest_url('family-archive/v1');
    ?>
    <div class="wrap">
        <h1>التحكم عن بعد</h1>
        <p>الصفحة دي لمفتاح API يسمح بالتحكم في الموقع (إضافة/تعديل/حذف أفراد، أحداث، وثائق، ملفات) من برّة لوحة التحكم.
        متشاركش المفتاح ده إلا مع حد بتثق فيه يدير الموقع نيابة عنك.</p>

        <table class="form-table">
            <tr>
                <th>رابط الـ API</th>
                <td><code><?php echo esc_html($endpoint); ?></code></td>
            </tr>
            <tr>
                <th>المفتاح الحالي</th>
                <td><code style="user-select:all"><?php echo esc_html($token); ?></code></td>
            </tr>
        </table>

        <form method="post">
            <?php wp_nonce_field('fa_remote_settings'); ?>
            <button type="submit" name="fa_regenerate_token" value="1" class="button" onclick="return confirm('لو عندك حد بيستخدم المفتاح القديم هيتوقف. متأكد؟');">إنشاء مفتاح جديد</button>
        </form>
    </div>
    <?php
}

function fa_rest_check_auth(WP_REST_Request $request)
{
    $header = $request->get_header('authorization');
    if (!$header || !preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
        return new WP_Error('fa_unauthorized', 'محتاج مفتاح API صحيح.', ['status' => 401]);
    }

    if (!hash_equals(fa_get_remote_token(), trim($matches[1]))) {
        return new WP_Error('fa_unauthorized', 'مفتاح API غلط.', ['status' => 401]);
    }

    return true;
}

add_action('rest_api_init', 'fa_register_rest_routes');

function fa_register_rest_routes(): void
{
    register_rest_route('family-archive/v1', '/status', [
        'methods' => 'GET',
        'callback' => 'fa_rest_status',
        'permission_callback' => 'fa_rest_check_auth',
    ]);

    fa_register_crud_routes('members', 'fa_member');
    fa_register_crud_routes('events', 'fa_event');
    fa_register_crud_routes('documents', 'fa_document');
    fa_register_crud_routes('files', 'fa_file');
}

function fa_register_crud_routes(string $slug, string $postType): void
{
    register_rest_route('family-archive/v1', "/{$slug}", [
        [
            'methods' => 'GET',
            'callback' => fn () => fa_rest_list($postType),
            'permission_callback' => 'fa_rest_check_auth',
        ],
        [
            'methods' => 'POST',
            'callback' => fn (WP_REST_Request $req) => fa_rest_upsert($postType, 0, $req),
            'permission_callback' => 'fa_rest_check_auth',
        ],
    ]);

    register_rest_route('family-archive/v1', "/{$slug}/(?P<id>\\d+)", [
        [
            'methods' => 'GET',
            'callback' => fn (WP_REST_Request $req) => fa_rest_get_one($postType, (int) $req['id']),
            'permission_callback' => 'fa_rest_check_auth',
        ],
        [
            'methods' => 'POST',
            'callback' => fn (WP_REST_Request $req) => fa_rest_upsert($postType, (int) $req['id'], $req),
            'permission_callback' => 'fa_rest_check_auth',
        ],
        [
            'methods' => 'DELETE',
            'callback' => fn (WP_REST_Request $req) => fa_rest_delete($postType, (int) $req['id']),
            'permission_callback' => 'fa_rest_check_auth',
        ],
    ]);
}

function fa_rest_status(): WP_REST_Response
{
    return new WP_REST_Response([
        'site' => get_bloginfo('name'),
        'plugin_version' => FA_VERSION,
        'wp_version' => get_bloginfo('version'),
        'counts' => [
            'members' => (int) wp_count_posts('fa_member')->publish,
            'events' => (int) wp_count_posts('fa_event')->publish,
            'documents' => (int) wp_count_posts('fa_document')->publish,
            'files' => (int) wp_count_posts('fa_file')->publish,
        ],
    ]);
}

function fa_post_type_meta_fields(string $postType): array
{
    return match ($postType) {
        'fa_member' => ['_fa_gender', '_fa_birth_year', '_fa_death_year', '_fa_father_id', '_fa_mother_id', '_fa_spouse_id'],
        'fa_event' => ['_fa_date_label', '_fa_sort_year', '_fa_related_member_id'],
        'fa_document' => ['_fa_category', '_fa_date_label', '_fa_sort_year', '_fa_related_member_id', '_fa_file', '_fa_file_original', '_fa_file_size'],
        'fa_file' => ['_fa_file', '_fa_file_original', '_fa_file_size'],
        default => [],
    };
}

function fa_rest_serialize_post(WP_Post $post): array
{
    $data = [
        'id' => $post->ID,
        'title' => $post->post_title,
        'content' => $post->post_content,
        'status' => $post->post_status,
    ];
    foreach (fa_post_type_meta_fields($post->post_type) as $key) {
        $data[substr($key, 4)] = get_post_meta($post->ID, $key, true);
    }
    if ($post->post_type === 'fa_member') {
        $thumbId = get_post_thumbnail_id($post);
        $data['photo_url'] = $thumbId ? wp_get_attachment_image_url($thumbId, 'medium') : null;
    }
    return $data;
}

function fa_rest_list(string $postType): WP_REST_Response
{
    $posts = get_posts(['post_type' => $postType, 'numberposts' => -1, 'post_status' => 'publish']);
    return new WP_REST_Response(array_map('fa_rest_serialize_post', $posts));
}

function fa_rest_get_one(string $postType, int $id)
{
    $post = get_post($id);
    if (!$post || $post->post_type !== $postType) {
        return new WP_Error('fa_not_found', 'مش موجود.', ['status' => 404]);
    }
    return new WP_REST_Response(fa_rest_serialize_post($post));
}

function fa_rest_delete(string $postType, int $id)
{
    $post = get_post($id);
    if (!$post || $post->post_type !== $postType) {
        return new WP_Error('fa_not_found', 'مش موجود.', ['status' => 404]);
    }
    if ($postType === 'fa_document') {
        fa_delete_secure_upload($id, 'documents');
    } elseif ($postType === 'fa_file') {
        fa_delete_secure_upload($id, 'files');
    }
    wp_delete_post($id, true);
    return new WP_REST_Response(['deleted' => true]);
}

function fa_rest_upsert(string $postType, int $id, WP_REST_Request $request)
{
    $params = $request->get_json_params() ?: $request->get_params();

    $existing = null;
    if ($id) {
        $existing = get_post($id);
        if (!$existing || $existing->post_type !== $postType) {
            return new WP_Error('fa_not_found', 'مش موجود.', ['status' => 404]);
        }
    }

    $postData = ['post_type' => $postType, 'post_status' => 'publish'];
    if (isset($params['title'])) {
        $postData['post_title'] = sanitize_text_field($params['title']);
    } elseif (!$existing) {
        return new WP_Error('fa_missing_title', 'العنوان مطلوب.', ['status' => 400]);
    }
    if (isset($params['content'])) {
        $postData['post_content'] = wp_kses_post($params['content']);
    }

    if ($existing) {
        $postData['ID'] = $id;
        wp_update_post($postData);
    } else {
        $id = wp_insert_post($postData, true);
        if (is_wp_error($id)) {
            return $id;
        }
    }

    fa_rest_apply_meta($postType, $id, $params);

    if ($postType === 'fa_member' && !empty($params['photo_base64'])) {
        fa_rest_set_member_photo($id, $params['photo_base64'], $params['photo_filename'] ?? 'photo.jpg');
    }

    if (in_array($postType, ['fa_document', 'fa_file'], true) && !empty($params['file_base64'])) {
        $sub = $postType === 'fa_document' ? 'documents' : 'files';
        $mimeMap = $postType === 'fa_document' ? FA_DOCUMENT_MIME_MAP : FA_FILE_MIME_MAP;
        $bytes = base64_decode($params['file_base64'], true);
        if ($bytes === false) {
            return new WP_Error('fa_bad_file', 'ملف base64 غلط.', ['status' => 400]);
        }
        $ok = fa_store_uploaded_bytes($id, $sub, $mimeMap, $bytes, $params['file_name'] ?? 'file');
        if (!$ok) {
            return new WP_Error('fa_upload_failed', 'تعذر حفظ الملف (نوع مش مدعوم أو حجم كبير).', ['status' => 400]);
        }
    }

    return new WP_REST_Response(fa_rest_serialize_post(get_post($id)), $existing ? 200 : 201);
}

function fa_rest_apply_meta(string $postType, int $id, array $params): void
{
    foreach (fa_post_type_meta_fields($postType) as $key) {
        $field = substr($key, 4);
        if (!array_key_exists($field, $params)) {
            continue;
        }
        $value = $params[$field];
        if (str_ends_with($field, '_id') || str_ends_with($field, '_year')) {
            $value = $value === '' || $value === null ? '' : absint($value);
        } elseif ($field === 'gender') {
            $value = $value === 'female' ? 'female' : 'male';
        } elseif ($field === 'category') {
            $value = in_array($value, ['waqf', 'inheritance', 'other'], true) ? $value : 'other';
        } else {
            $value = sanitize_text_field((string) $value);
        }
        update_post_meta($id, $key, $value);
    }

    if ($postType === 'fa_member' && !empty($params['spouse_id'])) {
        $spouseId = absint($params['spouse_id']);
        if ($spouseId && $spouseId !== $id) {
            update_post_meta($spouseId, '_fa_spouse_id', $id);
        }
    }
}

function fa_rest_set_member_photo(int $memberId, string $base64, string $filename): void
{
    $bytes = base64_decode($base64, true);
    if ($bytes === false) {
        return;
    }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $upload = wp_upload_bits(sanitize_file_name($filename), null, $bytes);
    if (!empty($upload['error'])) {
        return;
    }

    $filetype = wp_check_filetype($upload['file']);
    if (!in_array($filetype['type'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
        @unlink($upload['file']);
        return;
    }

    $attachmentId = wp_insert_attachment([
        'post_mime_type' => $filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_status' => 'inherit',
    ], $upload['file'], $memberId);

    $metadata = wp_generate_attachment_metadata($attachmentId, $upload['file']);
    wp_update_attachment_metadata($attachmentId, $metadata);
    set_post_thumbnail($memberId, $attachmentId);
}
