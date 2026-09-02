-- شغّل الملف ده من phpMyAdmin بعد ما تعمل قاعدة البيانات.
-- لو كنت شغّلت نسخة قديمة من الملف ده قبل كده، امسح القاعدة واعملها من جديد فاضية الأول.

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) DEFAULT NULL,
    role ENUM('admin', 'editor', 'viewer') NOT NULL DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) DEFAULT NULL,
    gender ENUM('male', 'female') NOT NULL DEFAULT 'male',
    birth_year SMALLINT UNSIGNED DEFAULT NULL,
    death_year SMALLINT UNSIGNED DEFAULT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    father_id INT UNSIGNED DEFAULT NULL,
    mother_id INT UNSIGNED DEFAULT NULL,
    spouse_id INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_father FOREIGN KEY (father_id) REFERENCES members(id) ON DELETE SET NULL,
    CONSTRAINT fk_mother FOREIGN KEY (mother_id) REFERENCES members(id) ON DELETE SET NULL,
    CONSTRAINT fk_spouse FOREIGN KEY (spouse_id) REFERENCES members(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    date_label VARCHAR(50) DEFAULT NULL,
    sort_year SMALLINT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    related_member_id INT UNSIGNED DEFAULT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_event_member FOREIGN KEY (related_member_id) REFERENCES members(id) ON DELETE SET NULL,
    CONSTRAINT fk_event_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    category ENUM('waqf', 'inheritance', 'other') NOT NULL DEFAULT 'other',
    date_label VARCHAR(50) DEFAULT NULL,
    sort_year SMALLINT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    file VARCHAR(255) DEFAULT NULL,
    related_member_id INT UNSIGNED DEFAULT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_document_member FOREIGN KEY (related_member_id) REFERENCES members(id) ON DELETE SET NULL,
    CONSTRAINT fk_document_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    file VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) DEFAULT NULL,
    file_size INT UNSIGNED DEFAULT NULL,
    uploaded_by INT UNSIGNED DEFAULT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_file_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- حساب أدمن افتراضي:
--   اسم المستخدم: admin
--   كلمة السر:   ChangeMe123!
-- غيّر كلمة السر فورًا بعد أول تسجيل دخول (من قايمة المستخدم فوق > تغيير كلمة السر).
INSERT INTO users (username, password_hash, display_name, role) VALUES
    ('admin', '$2y$12$6jPo5FSrZA2TCH/D4NuOfeAHLwSv67IrgYqytAy/Xa8LdopQjU3e2', 'المدير', 'admin');

-- بيانات تجريبية (اختياري) - عشان تشوف شكل الموقع، احذفها وحط بيانات عائلتك الحقيقية.
INSERT INTO members (id, first_name, last_name, gender, birth_year, death_year, bio) VALUES
    (1, 'أحمد', 'المصري', 'male', 1930, 2005, 'الجد الأكبر للعائلة.'),
    (2, 'فاطمة', 'حسن', 'female', 1935, 2010, NULL),
    (3, 'محمد', 'أحمد المصري', 'male', 1958, NULL, NULL),
    (4, 'سارة', 'أحمد المصري', 'female', 1961, NULL, NULL),
    (6, 'ليلى', 'عبد الله', 'female', 1960, NULL, NULL),
    (7, 'يوسف', 'محمد المصري', 'male', 1985, NULL, NULL);

UPDATE members SET spouse_id = 2 WHERE id = 1;
UPDATE members SET spouse_id = 1 WHERE id = 2;
UPDATE members SET father_id = 1, mother_id = 2, spouse_id = 6 WHERE id = 3;
UPDATE members SET father_id = 1, mother_id = 2 WHERE id = 4;
UPDATE members SET spouse_id = 3 WHERE id = 6;
UPDATE members SET father_id = 3, mother_id = 6 WHERE id = 7;

ALTER TABLE members AUTO_INCREMENT = 8;

INSERT INTO events (title, date_label, sort_year, description, related_member_id) VALUES
    ('ميلاد الجد أحمد', '1930', 1930, 'بداية الجيل الأول للعائلة.', 1),
    ('زواج أحمد وفاطمة', '1955', 1955, NULL, 1),
    ('انتقال العائلة للقاهرة', '1970', 1970, 'انتقلت العائلة من الصعيد للقاهرة.', NULL);

INSERT INTO documents (title, category, date_label, sort_year, description) VALUES
    ('حجة وقف أرض الجد أحمد', 'waqf', '1962', 1962, 'حجة وقف خيري على قطعة أرض زراعية، محفوظة بمحكمة الأسرة.'),
    ('إعلام وراثة أحمد المصري', 'inheritance', '2005', 2005, 'إعلام الوراثة الصادر بعد وفاة الجد أحمد وتوزيع التركة على الورثة.');

ALTER TABLE events AUTO_INCREMENT = 4;
ALTER TABLE documents AUTO_INCREMENT = 3;
