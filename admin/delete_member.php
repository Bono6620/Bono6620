<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);
$member = $id ? get_member($id) : null;

if ($member) {
    if (!empty($member['photo'])) {
        $path = UPLOAD_DIR . '/' . $member['photo'];
        if (is_file($path)) {
            @unlink($path);
        }
    }
    get_db()->prepare('DELETE FROM members WHERE id = ?')->execute([$id]);
}

header('Location: index.php');
exit;
