<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$files = get_all_files();
$pageTitle = 'الملفات';
$activeTab = 'files';
require __DIR__ . '/includes/header.php';
?>
<section class="tab-toolbar">
    <h1>ملفات العائلة</h1>
</section>

<?php if (can_edit()): ?>
<form method="post" action="file_save.php" enctype="multipart/form-data" class="stacked-form inline-form">
    <?= csrf_field() ?>
    <div class="form-row">
        <label>عنوان الملف
            <input type="text" name="title" required>
        </label>
        <label>الملف
            <input type="file" name="file" required accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt">
        </label>
    </div>
    <label>وصف مختصر (اختياري)
        <textarea name="description" rows="2"></textarea>
    </label>
    <button type="submit">رفع الملف</button>
</form>
<?php endif; ?>

<?php if (!$files): ?>
    <div class="empty-state"><p>لسه مفيش ملفات مرفوعة.</p></div>
<?php else: ?>
<div class="card-grid">
    <?php foreach ($files as $f): ?>
    <div class="item-card">
        <h3><?= e($f['title']) ?></h3>
        <?php if ($f['description']): ?><p><?= nl2br(e($f['description'])) ?></p><?php endif; ?>
        <p class="muted small">
            <?= e($f['original_name'] ?? '') ?>
            <?php if ($f['file_size']): ?> &middot; <?= e(format_bytes((int) $f['file_size'])) ?><?php endif; ?>
        </p>
        <div class="item-card-actions">
            <a class="button secondary" href="download.php?type=file&id=<?= (int) $f['id'] ?>">تحميل</a>
            <?php if (can_edit()): ?>
            <form method="post" action="file_delete.php" onsubmit="return confirm('متأكد إنك عايز تمسح <?= e($f['title']) ?>؟');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                <button type="submit" class="link-danger">حذف</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
