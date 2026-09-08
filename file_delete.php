<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: files.php');
    exit;
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);
$file = $id ? get_file_record($id) : null;

if ($file) {
    delete_uploaded_file(UPLOAD_DIR . '/files', $file['file']);
    get_db()->prepare('DELETE FROM files WHERE id = ?')->execute([$id]);
}

header('Location: files.php');
exit;
