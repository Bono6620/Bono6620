<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$member = $id ? get_member($id) : null;

if (!$member) {
    http_response_code(404);
    $pageTitle = 'غير موجود';
    $activeTab = 'tree';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty-state"><p>الشخص ده مش موجود.</p><p><a href="tree.php">رجوع للشجرة</a></p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = member_full_name($member);
$activeTab = 'tree';
$father = $member['father_id'] ? get_member((int) $member['father_id']) : null;
$mother = $member['mother_id'] ? get_member((int) $member['mother_id']) : null;
$spouse = $member['spouse_id'] ? get_member((int) $member['spouse_id']) : null;
$children = get_children((int) $member['id']);

require __DIR__ . '/includes/header.php';
?>
<article class="member-profile">
    <div class="member-profile-photo">
        <img src="<?= e(member_photo_url($member)) ?>" alt="<?= e(member_full_name($member)) ?>">
    </div>
    <div class="member-profile-info">
        <div class="profile-heading">
            <h1><?= e(member_full_name($member)) ?></h1>
            <?php if (can_edit()): ?>
                <div class="profile-actions">
                    <a class="button secondary" href="member_form.php?id=<?= (int) $member['id'] ?>">تعديل</a>
                    <form method="post" action="member_delete.php" onsubmit="return confirm('متأكد إنك عايز تمسح <?= e(member_full_name($member)) ?>؟');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
                        <button type="submit" class="button danger">حذف</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <?php if (member_years_label($member)): ?>
            <p class="years"><?= e(member_years_label($member)) ?></p>
        <?php endif; ?>

        <?php if (!empty($member['bio'])): ?>
            <p class="bio"><?= nl2br(e($member['bio'])) ?></p>
        <?php endif; ?>

        <dl class="relations">
            <?php if ($father): ?>
                <dt>الأب</dt>
                <dd><a href="member.php?id=<?= (int) $father['id'] ?>"><?= e(member_full_name($father)) ?></a></dd>
            <?php endif; ?>
            <?php if ($mother): ?>
                <dt>الأم</dt>
                <dd><a href="member.php?id=<?= (int) $mother['id'] ?>"><?= e(member_full_name($mother)) ?></a></dd>
            <?php endif; ?>
            <?php if ($spouse): ?>
                <dt>الزوج/الزوجة</dt>
                <dd><a href="member.php?id=<?= (int) $spouse['id'] ?>"><?= e(member_full_name($spouse)) ?></a></dd>
            <?php endif; ?>
        </dl>

        <?php if ($children): ?>
            <h2>الأبناء</h2>
            <ul class="children-list">
                <?php foreach ($children as $child): ?>
                    <li><a href="member.php?id=<?= (int) $child['id'] ?>"><?= e(member_full_name($child)) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p><a href="tree.php">&larr; رجوع للشجرة</a></p>
    </div>
</article>
<?php require __DIR__ . '/includes/footer.php'; ?>
