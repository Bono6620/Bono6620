<?php
/**
 * إعدادات وحدة "تسليم الورق المطلوب"
 * ------------------------------------------------------------------
 * عدّل القيم في هذا الملف فقط. باقي الملفات لا تحتاج أي تعديل.
 */

// ---------- فروع العائلة (الزراير الخمسة) ----------
// المفتاح (slug) بالإنجليزي لأنه بيتحط في الرابط واسم الفولدر،
// و "name" هو الاسم اللي بيظهر للناس على الزرار.
$SUBMIT_BRANCHES = array(
    'idris'      => array('name' => 'ادريس',      'letter' => 'ا'),
    'abdelwahed' => array('name' => 'عبد الواحد', 'letter' => 'ع'),
    'nazla'      => array('name' => 'ناظله',      'letter' => 'ن'),
    'zanouba'    => array('name' => 'زنوبه',      'letter' => 'ز'),
    'khadija'    => array('name' => 'خديجه',      'letter' => 'خ'),
);

// ---------- حدود الرفع ----------
// أقصى حجم للملف الواحد بالميجابايت.
define('SUBMIT_MAX_FILE_MB', 8);

// أقصى عدد ملفات في عملية الرفع الواحدة.
define('SUBMIT_MAX_FILES', 10);

// الامتدادات المسموح برفعها.
$SUBMIT_ALLOWED_EXT = array('jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf');

// أنواع الملفات المسموح بها (بيتأكد منها من محتوى الملف نفسه مش من اسمه).
$SUBMIT_ALLOWED_MIME = array(
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/webp'      => 'webp',
    'image/gif'       => 'gif',
    'application/pdf' => 'pdf',
);

// أقصى عدد عمليات رفع من نفس الـ IP في الساعة (حماية من السبام).
define('SUBMIT_RATE_LIMIT_PER_HOUR', 12);

// ---------- صلاحية الأدمن ----------
// الوحدة بتحاول تتعرف على الأدمن أوتوماتيك من الـ session بتاعة الموقع
// (is_admin / admin / role = admin ... إلخ).
// لو الأدمن مش ظاهر معاك في صفحة الإدارة، اكتب اسم المستخدم بتاعك هنا:
// مثال: array('bono', 'admin')
$SUBMIT_ADMIN_USERNAMES = array();

// ---------- مسارات ----------
// مكان تخزين الملفات المرفوعة (افتراضياً جوه فولدر data وهو محمي بـ .htaccess).
define('SUBMIT_DATA_DIR', __DIR__ . '/data');
define('SUBMIT_FILES_DIR', SUBMIT_DATA_DIR . '/files');
define('SUBMIT_INDEX_FILE', SUBMIT_DATA_DIR . '/submissions.json');

// مسار ملف الاستايل بتاع الموقع بالنسبة لفولدر submissions.
define('SUBMIT_SITE_CSS', '../assets/style.css');

// روابط الرجوع.
define('SUBMIT_LOGIN_URL', '../login.php');
define('SUBMIT_HOME_URL', '../index.php');

// اسم الموقع في الهيدر.
define('SUBMIT_SITE_TITLE', 'عائلة الخربوطلي');
