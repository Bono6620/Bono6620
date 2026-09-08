<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: timeline.php');
    exit;
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    get_db()->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
}

header('Location: timeline.php');
exit;
