<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$events = get_all_events();
$pageTitle = 'الأحداث الزمنية';
$activeTab = 'timeline';
require __DIR__ . '/includes/header.php';
?>
<section class="tab-toolbar">
    <h1>الأحداث الزمنية</h1>
    <?php if (can_edit()): ?>
        <a class="button" href="event_form.php">+ إضافة حدث</a>
    <?php endif; ?>
</section>

<?php if (!$events): ?>
    <div class="empty-state"><p>لسه مفيش أحداث مسجلة.</p></div>
<?php else: ?>
<ol class="timeline">
    <?php foreach ($events as $ev): ?>
    <li class="timeline-item">
        <div class="timeline-date"><?= e($ev['date_label'] ?: '؟') ?></div>
        <div class="timeline-content">
            <h3><?= e($ev['title']) ?></h3>
            <?php if ($ev['related_member_id']):
                $rm = get_member((int) $ev['related_member_id']);
            ?>
                <?php if ($rm): ?>
                    <p class="muted small">متعلق بـ <a href="member.php?id=<?= (int) $rm['id'] ?>"><?= e(member_full_name($rm)) ?></a></p>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($ev['description']): ?><p><?= nl2br(e($ev['description'])) ?></p><?php endif; ?>
            <?php if (can_edit()): ?>
            <div class="item-card-actions">
                <a href="event_form.php?id=<?= (int) $ev['id'] ?>">تعديل</a>
                <form method="post" action="event_delete.php" onsubmit="return confirm('متأكد إنك عايز تمسح الحدث ده؟');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $ev['id'] ?>">
                    <button type="submit" class="link-danger">حذف</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </li>
    <?php endforeach; ?>
</ol>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
