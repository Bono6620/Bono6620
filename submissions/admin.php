<?php
/**
 * صفحة الإدارة: فولدر لكل فرع، وجوّه الورق اللي اترفع.
 * مسموحة للأدمن بس.
 */

require_once __DIR__ . '/partials.php';

if (!s_is_logged_in()) {
    header('Location: ' . SUBMIT_LOGIN_URL);
    exit;
}

if (!s_is_admin()) {
    s_header('غير مسموح', array('subtitle' => 'الإدارة', 'back_url' => SUBMIT_HOME_URL, 'back_label' => 'رجوع للموقع'));
    echo '<div class="flash-error">الصفحة دي للأدمن بس.</div>';
    echo '<section class="form-section"><p class="hint-text">لو انت الأدمن وظاهرة لك الرسالة دي، افتح ملف '
       . '<code>submissions/config.php</code> وحط اسم المستخدم بتاعك في '
       . '<code>$SUBMIT_ADMIN_USERNAMES</code>.</p></section>';
    s_footer();
    exit;
}

$open = isset($_GET['b']) ? preg_replace('/[^a-z0-9_-]/', '', strtolower($_GET['b'])) : '';
$flash = array();

// ---------- حذف تسليم ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!s_csrf_check(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        $flash = array('error', 'الطلب مش صالح. حاول تاني.');
    } else {
        $id = isset($_POST['id']) ? preg_replace('/[^a-f0-9]/', '', $_POST['id']) : '';
        $removed = null;
        s_update_submissions(function ($data) use ($id, &$removed) {
            $out = array();
            foreach ($data as $item) {
                if (isset($item['id']) && $item['id'] === $id) {
                    $removed = $item;
                    continue;
                }
                $out[] = $item;
            }
            return $out;
        });
        if ($removed) {
            foreach ($removed['files'] as $f) {
                $path = SUBMIT_FILES_DIR . '/' . $removed['branch'] . '/' . basename($f['stored']);
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            $flash = array('success', 'تم حذف تسليم "' . $removed['name'] . '" والملفات بتاعته.');
        } else {
            $flash = array('error', 'التسليم ده مش موجود أصلاً.');
        }
    }
}

$counts = s_counts_by_branch();
$branch = $open ? s_branch($open) : null;

s_header(
    $branch ? ('فولدر ' . $branch['name']) : 'الورق المُسلَّم',
    array(
        'subtitle'   => 'إدارة الورق المُسلَّم',
        'back_url'   => $branch ? 'admin.php' : SUBMIT_HOME_URL,
        'back_label' => $branch ? 'رجوع للفولدرات' : 'رجوع للموقع',
    )
);

if ($flash) {
    s_flash($flash[0], $flash[1]);
}
?>

<?php if (!$branch): ?>

    <section class="hero">
        <h2>الورق المُسلَّم</h2>
        <p>كل فرع في فولدر لوحده. ادوس على الفولدر عشان تشوف الورق اللي اترفع فيه.</p>
    </section>

    <section class="branch-section">
        <div class="branch-grid">
            <?php foreach ($SUBMIT_BRANCHES as $slug => $b): ?>
                <?php $c = $counts[$slug]; ?>
                <a class="folder-btn<?php echo $c['files'] ? '' : ' is-empty'; ?>" href="admin.php?b=<?php echo s_e($slug); ?>">
                    <span class="folder-icon">📁</span>
                    <span class="folder-name"><?php echo s_e($b['name']); ?></span>
                    <span class="folder-meta">
                        <?php echo (int) $c['files']; ?> ملف · <?php echo (int) $c['people']; ?> تسليم
                    </span>
                    <span class="folder-date">
                        <?php echo $c['last'] ? ('آخر رفع: ' . s_date($c['last'])) : 'لسه فاضي'; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

<?php else: ?>

    <?php $items = s_submissions_for($open); ?>

    <section class="hero">
        <h2>فولدر <?php echo s_e($branch['name']); ?></h2>
        <p><?php echo count($items); ?> تسليم · <?php echo (int) $counts[$open]['files']; ?> ملف</p>
    </section>

    <section class="docs-section">
        <?php if (!$items): ?>
            <p class="hint-text">مفيش ورق اتسلّم في الفرع ده لحد دلوقتي.</p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <article class="submission-card">
                    <header class="submission-head">
                        <div>
                            <h3><?php echo s_e($item['name']); ?></h3>
                            <p class="submission-date"><?php echo s_e(s_date($item['time'])); ?> ·
                                <?php echo count($item['files']); ?> ملف</p>
                        </div>
                        <form method="post" class="submission-del"
                              onsubmit="return confirm('متأكد إنك عايز تمسح تسليم <?php echo s_e($item['name']); ?> وكل ملفاته؟');">
                            <input type="hidden" name="csrf" value="<?php echo s_e(s_csrf_token()); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo s_e($item['id']); ?>">
                            <button type="submit" class="btn-danger">حذف</button>
                        </form>
                    </header>

                    <?php if (!empty($item['note'])): ?>
                        <p class="submission-note"><?php echo s_e($item['note']); ?></p>
                    <?php endif; ?>

                    <div class="docs-grid">
                        <?php foreach ($item['files'] as $f): ?>
                            <?php $url = 'file.php?b=' . urlencode($open) . '&f=' . urlencode($f['stored']); ?>
                            <div class="doc-card">
                                <?php if (s_is_image($f['ext'])): ?>
                                    <a class="doc-thumb-link" href="<?php echo s_e($url); ?>" target="_blank" rel="noopener">
                                        <img class="doc-thumb" src="<?php echo s_e($url); ?>"
                                             alt="<?php echo s_e($f['original']); ?>" loading="lazy">
                                    </a>
                                <?php else: ?>
                                    <a class="doc-thumb-link doc-thumb-pdf" href="<?php echo s_e($url); ?>" target="_blank" rel="noopener">
                                        <span>PDF</span>
                                    </a>
                                <?php endif; ?>
                                <div class="doc-info">
                                    <p class="doc-title"><?php echo s_e($f['original']); ?></p>
                                    <p class="doc-date"><?php echo s_e(s_human_size($f['size'])); ?></p>
                                    <a class="btn-download" href="<?php echo s_e($url); ?>&dl=1">تحميل</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

<?php endif; ?>

<?php s_footer(); ?>
