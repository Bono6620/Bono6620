<?php
/**
 * دوال مساعدة لوحدة "تسليم الورق المطلوب".
 * الملف ده مبيطبعش أي حاجة، بس بيجهّز الدوال.
 */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** تهريب النصوص قبل عرضها في HTML. */
function s_e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

/** بيرجّع بيانات الفرع من الـ slug، أو null لو الفرع مش موجود. */
function s_branch($slug)
{
    global $SUBMIT_BRANCHES;
    return isset($SUBMIT_BRANCHES[$slug]) ? $SUBMIT_BRANCHES[$slug] : null;
}

/** بيتأكد إن فولدرات التخزين موجودة ومحمية. */
function s_ensure_storage()
{
    if (!is_dir(SUBMIT_DATA_DIR)) {
        @mkdir(SUBMIT_DATA_DIR, 0775, true);
    }
    if (!is_dir(SUBMIT_FILES_DIR)) {
        @mkdir(SUBMIT_FILES_DIR, 0775, true);
    }
    // حماية إضافية: منع الوصول المباشر للملفات من المتصفح.
    $htaccess = SUBMIT_DATA_DIR . '/.htaccess';
    if (!file_exists($htaccess)) {
        @file_put_contents($htaccess, "Require all denied\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n");
    }
    return is_dir(SUBMIT_FILES_DIR) && is_writable(SUBMIT_FILES_DIR);
}

/* ------------------------------------------------------------------ */
/* CSRF                                                                */
/* ------------------------------------------------------------------ */

function s_csrf_token()
{
    if (empty($_SESSION['submit_csrf'])) {
        $_SESSION['submit_csrf'] = bin2hex(s_random_bytes(16));
    }
    return $_SESSION['submit_csrf'];
}

function s_csrf_check($token)
{
    return !empty($_SESSION['submit_csrf'])
        && is_string($token)
        && hash_equals($_SESSION['submit_csrf'], $token);
}

/** بايتات عشوائية مع بدائل لو random_bytes مش متاحة على السيرفر. */
function s_random_bytes($len)
{
    if (function_exists('random_bytes')) {
        return random_bytes($len);
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        return openssl_random_pseudo_bytes($len);
    }
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $out .= chr(mt_rand(0, 255));
    }
    return $out;
}

/* ------------------------------------------------------------------ */
/* صلاحية الأدمن                                                       */
/* ------------------------------------------------------------------ */

/** هل المستخدم الحالي مسجّل دخول أصلاً؟ */
function s_is_logged_in()
{
    $keys = array('user', 'user_id', 'username', 'uid', 'logged_in', 'auth');
    foreach ($keys as $k) {
        if (!empty($_SESSION[$k])) {
            return true;
        }
    }
    return false;
}

/** اسم المستخدم الحالي من الـ session (لو موجود). */
function s_current_username()
{
    if (!empty($_SESSION['username']) && is_string($_SESSION['username'])) {
        return $_SESSION['username'];
    }
    if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
        foreach (array('username', 'name', 'user') as $k) {
            if (!empty($_SESSION['user'][$k]) && is_string($_SESSION['user'][$k])) {
                return $_SESSION['user'][$k];
            }
        }
    }
    if (!empty($_SESSION['user']) && is_string($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return '';
}

/**
 * هل المستخدم الحالي أدمن؟
 * بتتعرف أوتوماتيك على أشهر أشكال الـ session، وممكن كمان تتظبط يدوي
 * عن طريق $SUBMIT_ADMIN_USERNAMES في config.php.
 */
function s_is_admin()
{
    global $SUBMIT_ADMIN_USERNAMES;

    // 1) أسماء مستخدمين محددة يدوياً في الإعدادات.
    if (!empty($SUBMIT_ADMIN_USERNAMES)) {
        $me = s_current_username();
        if ($me !== '') {
            foreach ($SUBMIT_ADMIN_USERNAMES as $admin) {
                if (strcasecmp(trim($admin), trim($me)) === 0) {
                    return true;
                }
            }
        }
    }

    // 2) مفاتيح مباشرة في الـ session.
    foreach (array('is_admin', 'admin', 'isAdmin') as $k) {
        if (!empty($_SESSION[$k])) {
            return true;
        }
    }

    // 3) دور المستخدم.
    foreach (array('role', 'user_role', 'rank') as $k) {
        if (!empty($_SESSION[$k]) && is_string($_SESSION[$k])
            && in_array(strtolower($_SESSION[$k]), array('admin', 'administrator', 'owner'), true)) {
            return true;
        }
    }

    // 4) مصفوفة المستخدم.
    if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
        $u = $_SESSION['user'];
        foreach (array('is_admin', 'admin', 'isAdmin') as $k) {
            if (!empty($u[$k])) {
                return true;
            }
        }
        foreach (array('role', 'user_role', 'rank') as $k) {
            if (!empty($u[$k]) && is_string($u[$k])
                && in_array(strtolower($u[$k]), array('admin', 'administrator', 'owner'), true)) {
                return true;
            }
        }
    }

    return false;
}

/* ------------------------------------------------------------------ */
/* تخزين بيانات التسليمات (ملف JSON)                                   */
/* ------------------------------------------------------------------ */

/** بيقرأ كل التسليمات المسجّلة. */
function s_load_submissions()
{
    if (!file_exists(SUBMIT_INDEX_FILE)) {
        return array();
    }
    $raw = @file_get_contents(SUBMIT_INDEX_FILE);
    if ($raw === false || trim($raw) === '') {
        return array();
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : array();
}

/**
 * بينفّذ تعديل على ملف التسليمات مع قفل الملف،
 * عشان رفعتين في نفس اللحظة ما يمسحوش بعض.
 * $mutator: دالة بتاخد المصفوفة وترجّعها بعد التعديل.
 */
function s_update_submissions($mutator)
{
    s_ensure_storage();
    $fh = @fopen(SUBMIT_INDEX_FILE, 'c+');
    if (!$fh) {
        return false;
    }
    if (!flock($fh, LOCK_EX)) {
        fclose($fh);
        return false;
    }
    $raw = stream_get_contents($fh);
    $data = ($raw !== false && trim($raw) !== '') ? json_decode($raw, true) : array();
    if (!is_array($data)) {
        $data = array();
    }

    $data = call_user_func($mutator, $data);

    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return true;
}

/** تسليمات فرع معيّن، الأحدث الأول. */
function s_submissions_for($branch)
{
    $all = s_load_submissions();
    $out = array();
    foreach ($all as $item) {
        if (isset($item['branch']) && $item['branch'] === $branch) {
            $out[] = $item;
        }
    }
    usort($out, 's_sort_by_date_desc');
    return $out;
}

function s_sort_by_date_desc($a, $b)
{
    $ta = isset($a['time']) ? (int) $a['time'] : 0;
    $tb = isset($b['time']) ? (int) $b['time'] : 0;
    if ($ta === $tb) {
        return 0;
    }
    return ($ta < $tb) ? 1 : -1;
}

/** عدد الملفات في كل فرع. */
function s_counts_by_branch()
{
    global $SUBMIT_BRANCHES;
    $counts = array();
    foreach ($SUBMIT_BRANCHES as $slug => $b) {
        $counts[$slug] = array('people' => 0, 'files' => 0, 'last' => 0);
    }
    foreach (s_load_submissions() as $item) {
        $slug = isset($item['branch']) ? $item['branch'] : '';
        if (!isset($counts[$slug])) {
            continue;
        }
        $counts[$slug]['people']++;
        $counts[$slug]['files'] += isset($item['files']) ? count($item['files']) : 0;
        $t = isset($item['time']) ? (int) $item['time'] : 0;
        if ($t > $counts[$slug]['last']) {
            $counts[$slug]['last'] = $t;
        }
    }
    return $counts;
}

/* ------------------------------------------------------------------ */
/* حماية من السبام                                                     */
/* ------------------------------------------------------------------ */

function s_client_ip()
{
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

/** بيرجّع true لو الـ IP ده عدّى الحد المسموح بيه في الساعة الأخيرة. */
function s_rate_limited()
{
    s_ensure_storage();
    $file = SUBMIT_DATA_DIR . '/rate.json';
    $now = time();
    $ipKey = md5(s_client_ip());
    $limited = false;

    $fh = @fopen($file, 'c+');
    if (!$fh) {
        return false; // ما نمنعش الناس بسبب مشكلة في السيرفر
    }
    if (!flock($fh, LOCK_EX)) {
        fclose($fh);
        return false;
    }
    $raw = stream_get_contents($fh);
    $data = ($raw !== false && trim($raw) !== '') ? json_decode($raw, true) : array();
    if (!is_array($data)) {
        $data = array();
    }

    // تنضيف السجلات الأقدم من ساعة.
    foreach ($data as $k => $times) {
        $kept = array();
        foreach ((array) $times as $t) {
            if ($now - (int) $t < 3600) {
                $kept[] = (int) $t;
            }
        }
        if ($kept) {
            $data[$k] = $kept;
        } else {
            unset($data[$k]);
        }
    }

    $mine = isset($data[$ipKey]) ? $data[$ipKey] : array();
    if (count($mine) >= SUBMIT_RATE_LIMIT_PER_HOUR) {
        $limited = true;
    } else {
        $mine[] = $now;
        $data[$ipKey] = $mine;
    }

    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($data));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);

    return $limited;
}

/* ------------------------------------------------------------------ */
/* أدوات عامة                                                          */
/* ------------------------------------------------------------------ */

/** تنضيف الاسم اللي بيكتبه صاحب الورق. */
function s_clean_name($name)
{
    $name = trim((string) $name);
    $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);   // محارف تحكم
    $name = preg_replace('/\s+/u', ' ', $name);              // مسافات زيادة
    return mb_substr($name, 0, 80, 'UTF-8');
}

/** تنضيف الملاحظات. */
function s_clean_note($note)
{
    $note = trim((string) $note);
    $note = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $note);
    return mb_substr($note, 0, 500, 'UTF-8');
}

/** حجم الملف بصيغة مقروءة. */
function s_human_size($bytes)
{
    $bytes = (int) $bytes;
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' ميجا';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024) . ' كيلو';
    }
    return $bytes . ' بايت';
}

/** تاريخ بصيغة عربية بسيطة. */
function s_date($ts)
{
    if (!$ts) {
        return '—';
    }
    return date('Y/m/d - H:i', (int) $ts);
}

/** هل الملف صورة؟ */
function s_is_image($ext)
{
    return in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'webp', 'gif'), true);
}
