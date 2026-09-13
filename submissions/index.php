<?php
/**
 * صفحة اختيار الفرع — الزراير الخمسة.
 * الصفحة دي مفتوحة من غير تسجيل دخول عشان الناس تقدر تسلّم الورق.
 */

require_once __DIR__ . '/partials.php';

s_header('تسليم الورق المطلوب', array(
    'subtitle'   => 'اختار اسم جدّك الكبير',
    'back_url'   => SUBMIT_LOGIN_URL,
    'back_label' => 'رجوع لتسجيل الدخول',
));
?>

<section class="hero">
    <h2>تسليم الورق المطلوب</h2>
    <p>اختار الفرع اللي انت منه، وبعدين ارفع الورق واكتب اسمك.</p>
</section>

<section class="branch-section">
    <div class="branch-grid">
        <?php foreach ($SUBMIT_BRANCHES as $slug => $branch): ?>
            <a class="branch-btn" href="upload.php?b=<?php echo s_e($slug); ?>">
                <span class="branch-letter"><?php echo s_e($branch['letter']); ?></span>
                <span class="branch-name"><?php echo s_e($branch['name']); ?></span>
                <span class="branch-go">ارفع الورق ←</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php s_footer(); ?>
