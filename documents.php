<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$categoryFilter = $_GET['cat'] ?? '';
$allowedCats = ['waqf', 'inheritance', 'other'];
if (!in_array($categoryFilter, $allowedCats, true)) {
    $categoryFilter = '';
}

$documents = get_all_documents();
if ($categoryFilter !== '') {
    $documents = array_filter($documents, fn ($d) => $d['category'] === $categoryFilter);
}

$pageTitle = 'الوثائق';
$activeTab = 'documents';
require __DIR__ . '/includes/header.php';
?>
<section class="tab-toolbar">
    <h1>الوثائق (حجج الوقف والورث وغيرها)</h1>
    <?php if (can_edit()): ?>
        <a class="button" href="document_form.php">+ إضافة وثيقة</a>
    <?php endif; ?>
</section>

<div class="filter-tabs">
    <a href="documents.php" class="<?= $categoryFilter === '' ? 'is-active' : '' ?>">الكل</a>
    <a href="documents.php?cat=waqf" class="<?= $categoryFilter === 'waqf' ? 'is-active' : '' ?>">وقف</a>
    <a href="documents.php?cat=inheritance" class="<?= $categoryFilter === 'inheritance' ? 'is-active' : '' ?>">ورث</a>
    <a href="documents.php?cat=other" class="<?= $categoryFilter === 'other' ? 'is-active' : '' ?>">أخرى</a>
</div>

<?php if (!$documents): ?>
    <div class="empty-state"><p>لسه مفيش وثائق مسجلة في القسم ده.</p></div>
<?php else: ?>
<div class="card-grid">
    <?php foreach ($documents as $doc): ?>
    <div class="item-card">
        <span class="category-badge category-<?= e($doc['category']) ?>"><?= e(document_category_label($doc['category'])) ?></span>
        <h3><?= e($doc['title']) ?></h3>
        <p class="muted small"><?= e($doc['date_label'] ?: '') ?></p>
        <?php if ($doc['related_member_id']):
            $rm = get_member((int) $doc['related_member_id']);
        ?>
            <?php if ($rm): ?>
                <p class="muted small">متعلقة بـ <a href="member.php?id=<?= (int) $rm['id'] ?>"><?= e(member_full_name($rm)) ?></a></p>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($doc['description']): ?><p><?= nl2br(e($doc['description'])) ?></p><?php endif; ?>
        <div class="item-card-actions">
            <?php if ($doc['file']): ?>
                <a class="button secondary" href="download.php?type=document&id=<?= (int) $doc['id'] ?>">فتح المستند</a>
            <?php endif; ?>
            <?php if (can_edit()): ?>
                <a href="document_form.php?id=<?= (int) $doc['id'] ?>">تعديل</a>
                <form method="post" action="document_delete.php" onsubmit="return confirm('متأكد إنك عايز تمسح الوثيقة دي؟');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $doc['id'] ?>">
                    <button type="submit" class="link-danger">حذف</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
