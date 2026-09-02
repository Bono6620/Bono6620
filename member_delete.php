<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tree.php');
    exit;
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);
$member = $id ? get_member($id) : null;

if ($member) {
    delete_uploaded_file(UPLOAD_DIR, $member['photo'] ?? null);
    get_db()->prepare('DELETE FROM members WHERE id = ?')->execute([$id]);
}

header('Location: tree.php');
exit;
