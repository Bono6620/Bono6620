<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: documents.php');
    exit;
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);
$document = $id ? get_document($id) : null;

if ($document) {
    delete_uploaded_file(UPLOAD_DIR . '/documents', $document['file']);
    get_db()->prepare('DELETE FROM documents WHERE id = ?')->execute([$id]);
}

header('Location: documents.php');
exit;
