<?php
require_once __DIR__ . '/includes/auth.php';
require_edit();

$id = (int) ($_GET['id'] ?? 0);
$member = $id ? get_member($id) : null;
if ($id && !$member) {
    http_response_code(404);
    exit('الفرد ده مش موجود.');
}

$others = array_filter(get_all_members(), fn ($m) => (int) $m['id'] !== $id);

$pageTitle = $member ? 'تعديل فرد' : 'إضافة فرد';
$activeTab = 'tree';
require __DIR__ . '/includes/header.php';
?>
<h1><?= $member ? 'تعديل بيانات ' . e(member_full_name($member)) : 'إضافة فرد جديد' ?></h1>

<form method="post" action="member_save.php" enctype="multipart/form-data" class="stacked-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($member['id'] ?? 0) ?>">

    <div class="form-row">
        <label>الاسم الأول
            <input type="text" name="first_name" required value="<?= e($member['first_name'] ?? '') ?>">
        </label>
        <label>اسم العائلة
            <input type="text" name="last_name" value="<?= e($member['last_name'] ?? '') ?>">
        </label>
    </div>

    <div class="form-row">
        <label>النوع
            <select name="gender">
                <option value="male" <?= ($member['gender'] ?? 'male') === 'male' ? 'selected' : '' ?>>ذكر</option>
                <option value="female" <?= ($member['gender'] ?? '') === 'female' ? 'selected' : '' ?>>أنثى</option>
            </select>
        </label>
        <label>سنة الميلاد
            <input type="number" name="birth_year" min="1500" max="2100" value="<?= e((string) ($member['birth_year'] ?? '')) ?>">
        </label>
        <label>سنة الوفاة (لو متوفى)
            <input type="number" name="death_year" min="1500" max="2100" value="<?= e((string) ($member['death_year'] ?? '')) ?>">
        </label>
    </div>

    <div class="form-row">
        <label>الأب
            <select name="father_id">
                <option value="">-- بدون --</option>
                <?php foreach ($others as $o): if ($o['gender'] !== 'male') continue; ?>
                    <option value="<?= (int) $o['id'] ?>" <?= (int) ($member['father_id'] ?? 0) === (int) $o['id'] ? 'selected' : '' ?>><?= e(member_full_name($o)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>الأم
            <select name="mother_id">
                <option value="">-- بدون --</option>
                <?php foreach ($others as $o): if ($o['gender'] !== 'female') continue; ?>
                    <option value="<?= (int) $o['id'] ?>" <?= (int) ($member['mother_id'] ?? 0) === (int) $o['id'] ? 'selected' : '' ?>><?= e(member_full_name($o)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>الزوج/الزوجة
            <select name="spouse_id">
                <option value="">-- بدون --</option>
                <?php foreach ($others as $o): ?>
                    <option value="<?= (int) $o['id'] ?>" <?= (int) ($member['spouse_id'] ?? 0) === (int) $o['id'] ? 'selected' : '' ?>><?= e(member_full_name($o)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <label>نبذة / سيرة
        <textarea name="bio" rows="4"><?= e($member['bio'] ?? '') ?></textarea>
    </label>

    <label>الصورة الشخصية
        <input type="file" name="photo" accept="image/png,image/jpeg,image/webp">
    </label>
    <?php if (!empty($member['photo'])): ?>
        <img class="current-photo" src="<?= e(member_photo_url($member)) ?>" alt="" width="80">
    <?php endif; ?>

    <div class="form-actions">
        <button type="submit">حفظ</button>
        <a href="tree.php" class="button secondary">إلغاء</a>
    </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
