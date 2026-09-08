<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'الشجرة';
$activeTab = 'tree';
$roots = get_root_members();
require __DIR__ . '/includes/header.php';
?>
<section class="tab-toolbar">
    <input type="search" id="member-search" placeholder="ابحث عن اسم فرد من العائلة...">
    <?php if (can_edit()): ?>
        <a class="button" href="member_form.php">+ إضافة فرد</a>
    <?php endif; ?>
</section>

<?php if (!$roots): ?>
    <div class="empty-state">
        <p>لسه مفيش أفراد مضافين في الشجرة.</p>
        <?php if (can_edit()): ?>
            <p><a href="member_form.php">ابدأ بإضافة أول فرد</a> (عادة أقدم جد في العائلة).</p>
        <?php endif; ?>
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
