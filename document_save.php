<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: documents.php');
    exit;
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);
$existing = $id ? get_document($id) : null;
if ($id && !$existing) {
    http_response_code(404);
    exit('الوثيقة دي مش موجودة.');
}

$title = trim($_POST['title'] ?? '');
if ($title === '') {
    exit('عنوان الوثيقة مطلوب.');
}

$category = in_array($_POST['category'] ?? '', ['waqf', 'inheritance', 'other'], true) ? $_POST['category'] : 'other';
$dateLabel = trim($_POST['date_label'] ?? '') ?: null;
$sortYear = ($_POST['sort_year'] ?? '') !== '' ? (int) $_POST['sort_year'] : null;
$description = trim($_POST['description'] ?? '') ?: null;
$relatedMemberId = (int) ($_POST['related_member_id'] ?? 0) ?: null;

try {
    $file = handle_document_upload('file', $existing['file'] ?? null);
} catch (RuntimeException $ex) {
    exit(e($ex->getMessage()));
}

$db = get_db();
if ($existing) {
    $stmt = $db->prepare(
        'UPDATE documents SET title=?, category=?, date_label=?, sort_year=?, description=?, file=?, related_member_id=? WHERE id=?'
    );
    $stmt->execute([$title, $category, $dateLabel, $sortYear, $description, $file, $relatedMemberId, $id]);
} else {
    $user = current_user();
    $stmt = $db->prepare(
        'INSERT INTO documents (title, category, date_label, sort_year, description, file, related_member_id, created_by) VALUES (?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([$title, $category, $dateLabel, $sortYear, $description, $file, $relatedMemberId, $user['id']]);
}

header('Location: documents.php');
exit;
