<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tree.php');
    exit;
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);
$existing = $id ? get_member($id) : null;
if ($id && !$existing) {
    http_response_code(404);
    exit('الفرد ده مش موجود.');
}

$firstName = trim($_POST['first_name'] ?? '');
if ($firstName === '') {
    exit('الاسم الأول مطلوب.');
}

$lastName = trim($_POST['last_name'] ?? '') ?: null;
$gender = ($_POST['gender'] ?? 'male') === 'female' ? 'female' : 'male';
$birthYear = ($_POST['birth_year'] ?? '') !== '' ? (int) $_POST['birth_year'] : null;
$deathYear = ($_POST['death_year'] ?? '') !== '' ? (int) $_POST['death_year'] : null;
$bio = trim($_POST['bio'] ?? '') ?: null;

$fatherId = (int) ($_POST['father_id'] ?? 0) ?: null;
$motherId = (int) ($_POST['mother_id'] ?? 0) ?: null;
$spouseId = (int) ($_POST['spouse_id'] ?? 0) ?: null;

foreach ([$fatherId, $motherId, $spouseId] as $relId) {
    if ($relId !== null && $relId === $id) {
        exit('مش ممكن تختار نفس الشخص كأب أو أم أو زوج/ة.');
    }
}

try {
    $photo = handle_photo_upload('photo', $existing['photo'] ?? null);
} catch (RuntimeException $ex) {
    exit(e($ex->getMessage()));
}

$db = get_db();

if ($existing) {
    $stmt = $db->prepare(
        'UPDATE members SET first_name=?, last_name=?, gender=?, birth_year=?, death_year=?, bio=?, photo=?, father_id=?, mother_id=?, spouse_id=? WHERE id=?'
    );
    $stmt->execute([$firstName, $lastName, $gender, $birthYear, $deathYear, $bio, $photo, $fatherId, $motherId, $spouseId, $id]);
} else {
    $stmt = $db->prepare(
        'INSERT INTO members (first_name, last_name, gender, birth_year, death_year, bio, photo, father_id, mother_id, spouse_id) VALUES (?,?,?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([$firstName, $lastName, $gender, $birthYear, $deathYear, $bio, $photo, $fatherId, $motherId, $spouseId]);
    $id = (int) $db->lastInsertId();
}

if ($spouseId) {
    $db->prepare('UPDATE members SET spouse_id = ? WHERE id = ?')->execute([$id, $spouseId]);
}

header('Location: member.php?id=' . $id);
exit;
