<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

$users = get_db()->query('SELECT * FROM users ORDER BY username')->fetchAll();
$pageTitle = 'إدارة المستخدمين';
require __DIR__ . '/includes/header.php';
?>
<section class="tab-toolbar">
    <h1>إدارة المستخدمين</h1>
    <a class="button" href="user_form.php">+ إضافة مستخدم</a>
</section>

<p class="muted">
    <strong>مدير</strong>: كل الصلاحيات + إدارة المستخدمين. &nbsp;
    <strong>محرر</strong>: يقدر يضيف ويعدل ويمسح المحتوى. &nbsp;
    <strong>مشاهدة فقط</strong>: يشوف بس من غير تعديل.
</p>

<table class="admin-table">
    <thead>
        <tr>
            <th>الاسم</th>
            <th>اسم المستخدم</th>
            <th>الصلاحية</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= e($u['display_name'] ?: '-') ?></td>
            <td><?= e($u['username']) ?></td>
            <td><span class="role-badge role-<?= e($u['role']) ?>"><?= e(role_label($u['role'])) ?></span></td>
            <td class="row-actions">
                <a href="user_form.php?id=<?= (int) $u['id'] ?>">تعديل</a>
                <?php if ((int) $u['id'] !== (int) current_user()['id']): ?>
                <form method="post" action="user_delete.php" onsubmit="return confirm('متأكد إنك عايز تمسح المستخدم <?= e($u['username']) ?>؟');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="link-danger">حذف</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/includes/footer.php'; ?>
