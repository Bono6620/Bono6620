<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

$id = (int) ($_GET['id'] ?? 0);
$document = $id ? get_document($id) : null;
if ($id && !$document) {
    http_response_code(404);
    exit('الوثيقة دي مش موجودة.');
}

$members = get_all_members();
$pageTitle = $document ? 'تعديل وثيقة' : 'إضافة وثيقة';
$activeTab = 'documents';
require __DIR__ . '/includes/header.php';
?>
<h1><?= $document ? 'تعديل الوثيقة' : 'إضافة وثيقة جديدة' ?></h1>

<form method="post" action="document_save.php" enctype="multipart/form-data" class="stacked-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($document['id'] ?? 0) ?>">

    <label>عنوان الوثيقة
        <input type="text" name="title" required value="<?= e($document['title'] ?? '') ?>">
    </label>

    <div class="form-row">
        <label>النوع
            <select name="category">
                <option value="waqf" <?= ($document['category'] ?? '') === 'waqf' ? 'selected' : '' ?>>وقف</option>
                <option value="inheritance" <?= ($document['category'] ?? '') === 'inheritance' ? 'selected' : '' ?>>ورث</option>
                <option value="other" <?= ($document['category'] ?? 'other') === 'other' ? 'selected' : '' ?>>أخرى</option>
            </select>
        </label>
        <label>التاريخ (بأي صيغة)
            <input type="text" name="date_label" placeholder="مثال: 1962 أو 1390 هـ" value="<?= e($document['date_label'] ?? '') ?>">
        </label>
        <label>السنة (للترتيب فقط)
            <input type="number" name="sort_year" min="1" max="2100" value="<?= e((string) ($document['sort_year'] ?? '')) ?>">
        </label>
    </div>

    <label>مرتبطة بفرد من العائلة (اختياري)
        <select name="related_member_id">
            <option value="">-- بدون --</option>
            <?php foreach ($members as $m): ?>
                <option value="<?= (int) $m['id'] ?>" <?= (int) ($document['related_member_id'] ?? 0) === (int) $m['id'] ? 'selected' : '' ?>><?= e(member_full_name($m)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>الوصف (الوثيقة دي بتتكلم عن ايه)
        <textarea name="description" rows="4"><?= e($document['description'] ?? '') ?></textarea>
    </label>

    <label>صورة أو ملف PDF للوثيقة (اختياري)
        <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf">
    </label>
    <?php if (!empty($document['file'])): ?>
        <p class="muted small">فيه ملف مرفوع بالفعل. لو رفعت ملف جديد هيستبدله.</p>
    <?php endif; ?>

    <div class="form-actions">
        <button type="submit">حفظ</button>
        <a href="documents.php" class="button secondary">إلغاء</a>
    </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
