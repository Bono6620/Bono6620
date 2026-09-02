<?php
// إعدادات الموقع وقاعدة البيانات - عدّل القيم دي بعد ما ترفع الملفات على InfinityFree.
// هتلاقي بيانات قاعدة البيانات في لوحة تحكم InfinityFree تحت MySQL Databases.

define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_NAME', 'if0_XXXXXXXX_familytree');
define('DB_USER', 'if0_XXXXXXXX');
define('DB_PASS', 'CHANGE_ME');

define('SITE_NAME', 'شجرة عائلة');
define('SITE_URL', 'https://example.infinityfreeapp.com');

define('UPLOAD_DIR', __DIR__ . '/uploads');
define('UPLOAD_URL', 'uploads');
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);

date_default_timezone_set('Africa/Cairo');
session_start();
