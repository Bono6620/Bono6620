<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'الشجرة';
$roots = get_root_members();
require __DIR__ . '/includes/header.php';
?>
<section class="tree-toolbar">
    <input type="search" id="member-search" placeholder="ابحث عن اسم فرد من العائلة...">
</section>

<?php if (!$roots): ?>
    <div class="empty-state">
        <p>لسه مفيش أفراد مضافين في الشجرة.</p>
        <p><a href="admin/login.php">سجّل دخول كأدمن</a> عشان تبدأ تضيف أفراد العائلة.</p>
    </div>
<?php else: ?>
<div class="tree-wrapper">
    <div class="tree" id="family-tree">
        <ul>
            <?php foreach ($roots as $root): ?>
                <?= render_tree_node($root) ?>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
