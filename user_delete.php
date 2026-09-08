<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);
$db = get_db();

if ($id === (int) current_user()['id']) {
    exit('مينفعش تمسح حسابك انت وانت داخل بيه.');
}

$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($user) {
    if ($user['role'] === 'admin') {
        $adminCount = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        if ($adminCount <= 1) {
            exit('مينفعش تمسح آخر مدير في الموقع.');
        }
    }
    $db->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
}

header('Location: users.php');
exit;
