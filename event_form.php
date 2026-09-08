<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

$id = (int) ($_GET['id'] ?? 0);
$event = $id ? get_event($id) : null;
if ($id && !$event) {
    http_response_code(404);
    exit('الحدث ده مش موجود.');
}

$members = get_all_members();
$pageTitle = $event ? 'تعديل حدث' : 'إضافة حدث';
$activeTab = 'timeline';
require __DIR__ . '/includes/header.php';
?>
<h1><?= $event ? 'تعديل الحدث' : 'إضافة حدث جديد' ?></h1>

<form method="post" action="event_save.php" class="stacked-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($event['id'] ?? 0) ?>">

    <label>عنوان الحدث
        <input type="text" name="title" required value="<?= e($event['title'] ?? '') ?>">
    </label>

    <div class="form-row">
        <label>التاريخ (بأي صيغة تحب، سنة أو تاريخ كامل)
            <input type="text" name="date_label" placeholder="مثال: 1970 أو 3 مارس 1970" value="<?= e($event['date_label'] ?? '') ?>">
        </label>
        <label>السنة (لترتيب الأحداث فقط)
            <input type="number" name="sort_year" min="1" max="2100" value="<?= e((string) ($event['sort_year'] ?? '')) ?>">
        </label>
    </div>

    <label>مرتبط بفرد من العائلة (اختياري)
        <select name="related_member_id">
            <option value="">-- بدون --</option>
            <?php foreach ($members as $m): ?>
                <option value="<?= (int) $m['id'] ?>" <?= (int) ($event['related_member_id'] ?? 0) === (int) $m['id'] ? 'selected' : '' ?>><?= e(member_full_name($m)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>الوصف
        <textarea name="description" rows="4"><?= e($event['description'] ?? '') ?></textarea>
    </label>

    <div class="form-actions">
        <button type="submit">حفظ</button>
        <a href="timeline.php" class="button secondary">إلغاء</a>
    </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
