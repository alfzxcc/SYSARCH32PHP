-- ============================================================
-- SYSARCH32 - New Modules Database Migration
-- Run this in phpMyAdmin or MySQL CLI: source migration_new_modules.sql
-- ============================================================

USE sysarch32_db;

-- ─────────────────────────────────────────────────────────────
-- 1. RESERVATIONS TABLE
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reservations` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `id_number`        VARCHAR(30)  NOT NULL,
  `lab_room`         VARCHAR(50)  NOT NULL,
  `pc_number`        VARCHAR(20)  NOT NULL,
  `purpose`          VARCHAR(100) NOT NULL,
  `reservation_date` DATE         NOT NULL,
  `time_in`          TIME         NOT NULL,
  `time_out`         TIME         NOT NULL,
  `status`           ENUM('Pending','Approved','Rejected','Cancelled') DEFAULT 'Pending',
  `created_at`       DATETIME     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_id_number` (`id_number`),
  INDEX `idx_status`    (`status`),
  INDEX `idx_date_lab`  (`reservation_date`, `lab_room`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- 2. SOFTWARE TABLE
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `software` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `software_name` VARCHAR(100) NOT NULL,
  `version`       VARCHAR(30)  DEFAULT NULL,
  `category`      ENUM('Programming','Design','Office','Database','Networking','Utility') DEFAULT 'Utility',
  `description`   TEXT         DEFAULT NULL,
  `icon`          VARCHAR(60)  DEFAULT 'fas fa-cube',
  `created_at`    DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- 3. SOFTWARE_LABS TABLE (many-to-many: software ↔ lab rooms)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `software_labs` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `software_id`  INT         NOT NULL,
  `lab_room`     VARCHAR(50) NOT NULL,
  `is_available` TINYINT(1)  DEFAULT 1,
  UNIQUE KEY `uq_sw_lab` (`software_id`, `lab_room`),
  FOREIGN KEY (`software_id`) REFERENCES `software`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- 4. TESTIMONIALS TABLE
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `id_number`   VARCHAR(30) NOT NULL,
  `message`     TEXT        NOT NULL,
  `rating`      TINYINT(1)  DEFAULT 5 CHECK (`rating` BETWEEN 1 AND 5),
  `is_approved` TINYINT(1)  DEFAULT 0,
  `created_at`  DATETIME    DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME    DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_approved` (`is_approved`),
  INDEX `idx_id_number` (`id_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- 5. LAB_COMPUTERS TABLE (for software_availability page)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lab_computers` (
  `id`       INT AUTO_INCREMENT PRIMARY KEY,
  `lab_room` VARCHAR(50) NOT NULL,
  `pc_number` VARCHAR(20) NOT NULL,
  `status`   ENUM('available','in-use','maintenance') DEFAULT 'available',
  UNIQUE KEY `uq_lab_pc` (`lab_room`, `pc_number`),
  INDEX `idx_lab_status` (`lab_room`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- 6. SITIN_RECORDS TABLE — ensure time_out column exists
--    (your existing table may only have login_time; this adds 
--     the columns the summary/analytics pages expect)
-- ─────────────────────────────────────────────────────────────
ALTER TABLE `sitin_records`
  ADD COLUMN IF NOT EXISTS `time_out`   DATETIME DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `pc_number`  VARCHAR(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `lab_room`   VARCHAR(50) DEFAULT NULL;

-- ─────────────────────────────────────────────────────────────
-- 7. SEED: Default lab computers (40 PCs per lab)
-- ─────────────────────────────────────────────────────────────
INSERT IGNORE INTO `lab_computers` (`lab_room`, `pc_number`, `status`)
SELECT 
    lab, CONCAT('PC-', LPAD(n, 2, '0')),
    CASE WHEN RAND() < 0.75 THEN 'available' WHEN RAND() < 0.9 THEN 'in-use' ELSE 'maintenance' END
FROM (
    SELECT 'Lab 524' AS lab UNION SELECT 'Lab 526' UNION SELECT 'Lab 542' UNION SELECT 'Lab 544'
) AS labs
CROSS JOIN (
    SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION
    SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION
    SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION
    SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION
    SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION
    SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION SELECT 30 UNION
    SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION
    SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION SELECT 40
) AS nums;

-- ─────────────────────────────────────────────────────────────
-- 8. SEED: Default software entries
-- ─────────────────────────────────────────────────────────────
INSERT IGNORE INTO `software` (`software_name`, `version`, `category`, `icon`) VALUES
('Visual Studio Code',  '1.89',      'Programming', 'fas fa-code'),
('NetBeans IDE',        '21.0',      'Programming', 'fas fa-code-branch'),
('IntelliJ IDEA',       '2024.1',    'Programming', 'fas fa-brain'),
('Python 3.12',         '3.12',      'Programming', 'fab fa-python'),
('Node.js',             '20 LTS',    'Programming', 'fab fa-node-js'),
('Eclipse IDE',         '2024-03',   'Programming', 'fas fa-circle-notch'),
('Adobe Photoshop',     'CS6',       'Design',      'fas fa-paint-brush'),
('Adobe Illustrator',   'CS6',       'Design',      'fas fa-bezier-curve'),
('Figma',               'Web',       'Design',      'fas fa-vector-square'),
('Microsoft Office',    '2021',      'Office',      'fas fa-file-word'),
('XAMPP',               '8.2.12',    'Database',    'fas fa-database'),
('MySQL Workbench',     '8.0',       'Database',    'fas fa-table'),
('Wireshark',           '4.2',       'Networking',  'fas fa-network-wired'),
('Cisco Packet Tracer', '8.2',       'Networking',  'fas fa-project-diagram'),
('7-Zip',               '24.0',      'Utility',     'fas fa-file-archive'),
('Google Chrome',       'Latest',    'Utility',     'fab fa-chrome');

-- Assign software to labs
INSERT IGNORE INTO `software_labs` (`software_id`, `lab_room`, `is_available`)
SELECT s.id, l.lab_room, 1
FROM `software` s
CROSS JOIN (
    SELECT 'Lab 524' AS lab_room UNION SELECT 'Lab 544'
) AS l
WHERE s.category IN ('Programming','Database','Utility');

INSERT IGNORE INTO `software_labs` (`software_id`, `lab_room`, `is_available`)
SELECT s.id, 'Lab 526', 1
FROM `software` s
WHERE s.category IN ('Design','Utility','Office');

INSERT IGNORE INTO `software_labs` (`software_id`, `lab_room`, `is_available`)
SELECT s.id, 'Lab 542', 1
FROM `software` s
WHERE s.category IN ('Networking','Utility','Office');

-- ─────────────────────────────────────────────────────────────
-- Done!
-- ─────────────────────────────────────────────────────────────
SELECT 'Migration complete! New tables: reservations, software, software_labs, testimonials, lab_computers' AS status;

-- ─────────────────────────────────────────────────────────────
-- 9. SYSTEM_SETTINGS TABLE (for admin enable/disable features)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key`   VARCHAR(100) UNIQUE NOT NULL,
  `setting_value` VARCHAR(255) NOT NULL,
  `updated_at`    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) VALUES
  ('reservations_enabled', '1');
