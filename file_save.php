<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: files.php');
    exit;
}

csrf_check();

$title = trim($_POST['title'] ?? '');
if ($title === '') {
    exit('عنوان الملف مطلوب.');
}
$description = trim($_POST['description'] ?? '') ?: null;

if (empty($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    exit('لازم تختار ملف.');
}

$originalName = $_FILES['file']['name'] ?? null;
$size = $_FILES['file']['size'] ?? null;

try {
    $filename = handle_generic_file_upload('file');
} catch (RuntimeException $ex) {
    exit(e($ex->getMessage()));
}

$user = current_user();
$stmt = get_db()->prepare(
    'INSERT INTO files (title, description, file, original_name, file_size, uploaded_by) VALUES (?,?,?,?,?,?)'
);
$stmt->execute([$title, $description, $filename, $originalName, $size, $user['id']]);

header('Location: files.php');
exit;
