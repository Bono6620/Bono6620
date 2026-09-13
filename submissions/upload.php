<?php
/**
 * صفحة رفع الورق لفرع معيّن + معالجة الرفع.
 * مفتوحة من غير تسجيل دخول، ومحميّة بـ CSRF وفلترة للملفات وحد أقصى للرفع.
 */

require_once __DIR__ . '/partials.php';

$slug   = isset($_GET['b']) ? preg_replace('/[^a-z0-9_-]/', '', strtolower($_GET['b'])) : '';
$branch = s_branch($slug);

if (!$branch) {
    header('Location: index.php');
    exit;
}

$errors = array();
$done   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    $name  = s_clean_name(isset($_POST['sender_name']) ? $_POST['sender_name'] : '');
    $note  = s_clean_note(isset($_POST['note']) ? $_POST['note'] : '');
    $trap  = isset($_POST['website']) ? trim($_POST['website']) : ''; // خانة مخفية للبوتات

    if (!s_csrf_check($token)) {
        $errors[] = 'الصفحة قعدت مفتوحة فترة طويلة. اعمل تحديث للصفحة وحاول تاني.';
    } elseif ($trap !== '') {
        $errors[] = 'حصلت مشكلة في إرسال النموذج.';
    } elseif ($name === '') {
        $errors[] = 'لازم تكتب اسمك.';
    } elseif (mb_strlen($name, 'UTF-8') < 3) {
        $errors[] = 'اكتب اسمك كامل من فضلك.';
    } elseif (empty($_FILES['docs']) || !isset($_FILES['docs']['name'])) {
        $errors[] = 'لازم ترفع ملف واحد على الأقل.';
    } elseif (s_rate_limited()) {
        $errors[] = 'اترفع عدد كبير من الملفات من عندك دلوقتي. استنى شوية وحاول تاني.';
    }

    if (!$errors && !s_ensure_storage()) {
        $errors[] = 'مكان التخزين على السيرفر مش متاح. كلّم مسؤول الموقع.';
    }

    if (!$errors) {
        $files   = $_FILES['docs'];
        $count   = is_array($files['name']) ? count($files['name']) : 0;
        $maxSize = SUBMIT_MAX_FILE_MB * 1024 * 1024;
        $saved   = array();

        if ($count === 0) {
            $errors[] = 'لازم ترفع ملف واحد على الأقل.';
        } elseif ($count > SUBMIT_MAX_FILES) {
            $errors[] = 'أقصى عدد ملفات في المرة الواحدة هو ' . SUBMIT_MAX_FILES . ' ملفات.';
        }

        for ($i = 0; $i < $count && !$errors; $i++) {
            $err      = $files['error'][$i];
            $origName = (string) $files['name'][$i];
            $tmp      = $files['tmp_name'][$i];
            $size     = (int) $files['size'][$i];

            if ($err === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($err !== UPLOAD_ERR_OK) {
                $errors[] = 'الملف "' . $origName . '" مترفعش. جرّب تاني أو صغّر حجمه.';
                break;
            }
            if (!is_uploaded_file($tmp)) {
                $errors[] = 'ملف غير صالح.';
                break;
            }
            if ($size <= 0) {
                $errors[] = 'الملف "' . $origName . '" فاضي.';
                break;
            }
            if ($size > $maxSize) {
                $errors[] = 'الملف "' . $origName . '" أكبر من ' . SUBMIT_MAX_FILE_MB . ' ميجا.';
                break;
            }

            // التأكد من نوع الملف من محتواه مش من اسمه.
            $mime = '';
            if (function_exists('finfo_open')) {
                $fi = finfo_open(FILEINFO_MIME_TYPE);
                if ($fi) {
                    $mime = (string) finfo_file($fi, $tmp);
                    finfo_close($fi);
                }
            }
            if ($mime === '' && function_exists('mime_content_type')) {
                $mime = (string) mime_content_type($tmp);
            }

            if (!isset($SUBMIT_ALLOWED_MIME[$mime])) {
                $errors[] = 'نوع الملف "' . $origName . '" مش مسموح بيه. المسموح: صور (jpg, png, webp) أو PDF.';
                break;
            }
            $ext = $SUBMIT_ALLOWED_MIME[$mime];

            // اسم تخزين عشوائي، فالاسم الأصلي عمره ما بيتنفّذ على السيرفر.
            $storedName = date('Ymd-His') . '-' . bin2hex(s_random_bytes(8)) . '.' . $ext;
            $dest       = SUBMIT_FILES_DIR . '/' . $slug . '/' . $storedName;

            if (!is_dir(dirname($dest)) && !@mkdir(dirname($dest), 0775, true)) {
                $errors[] = 'مش قادر أجهّز فولدر الفرع على السيرفر.';
                break;
            }
            if (!@move_uploaded_file($tmp, $dest)) {
                $errors[] = 'مش قادر أحفظ الملف "' . $origName . '" على السيرفر.';
                break;
            }
            @chmod($dest, 0644);

            $saved[] = array(
                'stored'   => $storedName,
                'original' => mb_substr(preg_replace('/[\x00-\x1F\x7F\/\\\\]/u', '', $origName), 0, 120, 'UTF-8'),
                'ext'      => $ext,
                'size'     => $size,
            );
        }

        if (!$errors && !$saved) {
            $errors[] = 'لازم ترفع ملف واحد على الأقل.';
        }

        if ($errors) {
            // لو حصل خطأ في نص الطريق، نمسح اللي اترفع عشان ما يفضلش ملفات معلّقة.
            foreach ($saved as $f) {
                @unlink(SUBMIT_FILES_DIR . '/' . $slug . '/' . $f['stored']);
            }
        } else {
            $entry = array(
                'id'     => bin2hex(s_random_bytes(8)),
                'branch' => $slug,
                'name'   => $name,
                'note'   => $note,
                'time'   => time(),
                'ip'     => s_client_ip(),
                'files'  => $saved,
            );
            s_update_submissions(function ($data) use ($entry) {
                $data[] = $entry;
                return $data;
            });

            // توكن جديد ومنع إعادة الإرسال بالتحديث.
            unset($_SESSION['submit_csrf']);
            $_SESSION['submit_done'] = array(
                'name'  => $name,
                'count' => count($saved),
                'branch' => $slug,
            );
            header('Location: upload.php?b=' . urlencode($slug) . '&done=1');
            exit;
        }
    }
}

if (isset($_GET['done']) && !empty($_SESSION['submit_done'])) {
    $done = $_SESSION['submit_done'];
    unset($_SESSION['submit_done']);
}

$maxBytesHint = SUBMIT_MAX_FILE_MB * 1024 * 1024;

s_header('رفع الورق — ' . $branch['name'], array(
    'subtitle'   => 'فرع ' . $branch['name'],
    'back_url'   => 'index.php',
    'back_label' => 'رجوع للفروع',
));
?>

<section class="hero">
    <h2>ارفع الورق المطلوب</h2>
    <p>فرع <strong><?php echo s_e($branch['name']); ?></strong> — ارفع صور الورق أو ملف PDF واكتب اسمك.</p>
</section>

<?php if ($done): ?>
    <div class="flash-success">
        تم استلام <?php echo (int) $done['count']; ?> ملف باسم "<?php echo s_e($done['name']); ?>". شكراً ليك.
    </div>
    <section class="form-section">
        <div class="done-box">
            <p>الورق وصل للإدارة وهيتراجع قريب.</p>
            <div class="done-actions">
                <a class="btn-chip" href="upload.php?b=<?php echo s_e($slug); ?>">رفع ورق تاني</a>
                <a class="btn-chip" href="index.php">رجوع للفروع</a>
            </div>
        </div>
    </section>
<?php else: ?>

    <?php foreach ($errors as $err): ?>
        <div class="flash-error"><?php echo s_e($err); ?></div>
    <?php endforeach; ?>

    <section class="form-section">
        <h3 class="section-title">بيانات التسليم</h3>
        <form class="admin-form" method="post" enctype="multipart/form-data" id="upload-form">
            <input type="hidden" name="csrf" value="<?php echo s_e(s_csrf_token()); ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo (int) $maxBytesHint; ?>">
            <div class="hp-field" aria-hidden="true">
                <label>لا تكتب هنا <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <label>
                <span>اسمك بالكامل <em class="req">*</em></span>
                <input type="text" name="sender_name" required maxlength="80"
                       value="<?php echo isset($_POST['sender_name']) ? s_e(s_clean_name($_POST['sender_name'])) : ''; ?>"
                       placeholder="مثال: محمد أحمد الخربوطلي">
            </label>

            <label>
                <span>الورق المطلوب <em class="req">*</em></span>
                <input type="file" name="docs[]" id="docs" multiple required
                       accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,image/*,application/pdf">
                <small class="field-hint">
                    تقدر تختار أكتر من ملف مرة واحدة — صور أو PDF، أقصى حجم للملف
                    <?php echo (int) SUBMIT_MAX_FILE_MB; ?> ميجا، وأقصى عدد <?php echo (int) SUBMIT_MAX_FILES; ?> ملفات.
                </small>
                <div id="file-list" class="file-list"></div>
            </label>

            <label>
                <span>ملاحظات (اختياري)</span>
                <input type="text" name="note" maxlength="500" placeholder="أي كلام حابب توصّله مع الورق"
                       value="<?php echo isset($_POST['note']) ? s_e(s_clean_note($_POST['note'])) : ''; ?>">
            </label>

            <button type="submit" class="btn-primary">تسليم الورق</button>
        </form>
    </section>

    <script>
    (function () {
        var input = document.getElementById('docs');
        var list  = document.getElementById('file-list');
        var form  = document.getElementById('upload-form');
        var maxFiles = <?php echo (int) SUBMIT_MAX_FILES; ?>;
        var maxBytes = <?php echo (int) $maxBytesHint; ?>;
        if (!input || !list) { return; }

        input.addEventListener('change', function () {
            list.innerHTML = '';
            var files = input.files || [];
            for (var i = 0; i < files.length; i++) {
                var row = document.createElement('div');
                row.className = 'file-row';
                var mb = (files[i].size / 1048576).toFixed(1);
                row.textContent = files[i].name + ' (' + mb + ' ميجا)';
                if (files[i].size > maxBytes) {
                    row.className += ' too-big';
                    row.textContent += ' — أكبر من المسموح';
                }
                list.appendChild(row);
            }
            if (files.length > maxFiles) {
                var warn = document.createElement('div');
                warn.className = 'file-row too-big';
                warn.textContent = 'اخترت ' + files.length + ' ملفات، المسموح ' + maxFiles + ' بس.';
                list.appendChild(warn);
            }
        });

        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = 'جاري الرفع...'; }
        });
    })();
    </script>

<?php endif; ?>

<?php s_footer(); ?>
