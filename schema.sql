-- شغّل الملف ده من phpMyAdmin بتاع InfinityFree بعد ما تعمل قاعدة البيانات.

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
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

-- حساب أدمن افتراضي:
--   اسم المستخدم: admin
--   كلمة السر:   ChangeMe123!
-- غيّر كلمة السر فورًا بعد أول تسجيل دخول من صفحة "تغيير كلمة السر".
INSERT INTO admins (username, password_hash) VALUES
    ('admin', '$2y$12$6jPo5FSrZA2TCH/D4NuOfeAHLwSv67IrgYqytAy/Xa8LdopQjU3e2');

-- بيانات تجريبية (اختياري) - عشان تشوف شكل الشجرة، احذفها وحط بيانات عائلتك الحقيقية.
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
