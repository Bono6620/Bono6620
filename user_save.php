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

$existing = null;
if ($id) {
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch() ?: null;
    if (!$existing) {
        http_response_code(404);
        exit('المستخدم ده مش موجود.');
    }
}

$displayName = trim($_POST['display_name'] ?? '') ?: null;
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = in_array($_POST['role'] ?? '', ['admin', 'editor', 'viewer'], true) ? $_POST['role'] : 'viewer';

if ($username === '') {
    exit('اسم المستخدم مطلوب.');
}

if ($existing && $existing['role'] === 'admin' && $role !== 'admin') {
    $adminCount = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    if ($adminCount <= 1) {
        exit('مينفعش تشيل صلاحية المدير من آخر مدير في الموقع.');
    }
}

if ($existing) {
    if ($password !== '') {
        if (strlen($password) < 8) {
            exit('كلمة السر لازم تكون 8 حروف على الأقل.');
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare('UPDATE users SET display_name=?, role=?, password_hash=? WHERE id=?');
        $stmt->execute([$displayName, $role, $hash, $id]);
    } else {
        $stmt = $db->prepare('UPDATE users SET display_name=?, role=? WHERE id=?');
        $stmt->execute([$displayName, $role, $id]);
    }
} else {
    if (strlen($password) < 8) {
        exit('كلمة السر لازم تكون 8 حروف على الأقل.');
    }
    $check = $db->prepare('SELECT id FROM users WHERE username = ?');
    $check->execute([$username]);
    if ($check->fetch()) {
        exit('اسم المستخدم ده مستخدم قبل كده.');
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare('INSERT INTO users (username, password_hash, display_name, role) VALUES (?,?,?,?)');
    $stmt->execute([$username, $hash, $displayName, $role]);
}

header('Location: users.php');
exit;
