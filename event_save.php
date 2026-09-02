<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: timeline.php');
    exit;
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);
$existing = $id ? get_event($id) : null;
if ($id && !$existing) {
    http_response_code(404);
    exit('الحدث ده مش موجود.');
}

$title = trim($_POST['title'] ?? '');
if ($title === '') {
    exit('عنوان الحدث مطلوب.');
}
$dateLabel = trim($_POST['date_label'] ?? '') ?: null;
$sortYear = ($_POST['sort_year'] ?? '') !== '' ? (int) $_POST['sort_year'] : null;
$description = trim($_POST['description'] ?? '') ?: null;
$relatedMemberId = (int) ($_POST['related_member_id'] ?? 0) ?: null;

$db = get_db();
if ($existing) {
    $stmt = $db->prepare('UPDATE events SET title=?, date_label=?, sort_year=?, description=?, related_member_id=? WHERE id=?');
    $stmt->execute([$title, $dateLabel, $sortYear, $description, $relatedMemberId, $id]);
} else {
    $user = current_user();
    $stmt = $db->prepare('INSERT INTO events (title, date_label, sort_year, description, related_member_id, created_by) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$title, $dateLabel, $sortYear, $description, $relatedMemberId, $user['id']]);
}

header('Location: timeline.php');
exit;
