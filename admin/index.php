<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$members = get_all_members();
$pageTitle = 'لوحة التحكم';
$assetPrefix = '../';
require __DIR__ . '/../includes/header.php';
?>
<section class="admin-bar">
    <h1>لوحة التحكم</h1>
    <div class="admin-actions">
        <a class="button" href="member_form.php">+ إضافة فرد جديد</a>
        <a class="button secondary" href="change_password.php">تغيير كلمة السر</a>
        <a class="button secondary" href="logout.php">تسجيل خروج</a>
    </div>
</section>

<?php if (!$members): ?>
    <p>لسه مفيش أفراد. ابدأ بإضافة أول فرد (عادة أقدم جد في العائلة).</p>
<?php else: ?>
<table class="admin-table">
    <thead>
        <tr>
            <th>الاسم</th>
            <th>النوع</th>
            <th>سنة الميلاد</th>
            <th>الأب</th>
            <th>الأم</th>
            <th>الزوج/الزوجة</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($members as $m): ?>
        <tr>
            <td><a href="../member.php?id=<?= (int) $m['id'] ?>"><?= e(member_full_name($m)) ?></a></td>
            <td><?= $m['gender'] === 'female' ? 'أنثى' : 'ذكر' ?></td>
            <td><?= e((string) ($m['birth_year'] ?? '')) ?></td>
            <td><?php $f = $m['father_id'] ? get_member((int) $m['father_id']) : null; ?><?= $f ? e(member_full_name($f)) : '-' ?></td>
            <td><?php $mo = $m['mother_id'] ? get_member((int) $m['mother_id']) : null; ?><?= $mo ? e(member_full_name($mo)) : '-' ?></td>
            <td><?php $sp = $m['spouse_id'] ? get_member((int) $m['spouse_id']) : null; ?><?= $sp ? e(member_full_name($sp)) : '-' ?></td>
            <td class="row-actions">
                <a href="member_form.php?id=<?= (int) $m['id'] ?>">تعديل</a>
                <form method="post" action="delete_member.php" onsubmit="return confirm('متأكد إنك عايز تمسح <?= e(member_full_name($m)) ?>؟');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                    <button type="submit" class="link-danger">حذف</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
