-- ====================================================
-- setup_notifications.sql
-- شغّل هذا الملف في phpMyAdmin مرة واحدة فقط
-- ====================================================

-- 1. جدول الإشعارات الداخلية
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT NOT NULL,
    `type`       VARCHAR(50)  NOT NULL,
    `title`      VARCHAR(255) NOT NULL,
    `message`    TEXT         NOT NULL,
    `is_read`    TINYINT(1)   DEFAULT 0,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. إضافة user_id لجدول الاستشارات (إن لم يكن موجوداً)
ALTER TABLE `midecal_consultation`
    ADD COLUMN IF NOT EXISTS `user_id` INT DEFAULT NULL AFTER `id`;

-- 3. إضافة عمود user_email للاستشارات (لإرسال الرد بالبريد)
ALTER TABLE `midecal_consultation`
    ADD COLUMN IF NOT EXISTS `user_email` VARCHAR(255) DEFAULT NULL AFTER `user_id`;

-- 4. إضافة عمود reply (رد الصيدلاني على الاستشارة)
ALTER TABLE `midecal_consultation`
    ADD COLUMN IF NOT EXISTS `reply` TEXT DEFAULT NULL AFTER `p_notes`;

-- 5. إضافة عمود replied_at (وقت الرد)
ALTER TABLE `midecal_consultation`
    ADD COLUMN IF NOT EXISTS `replied_at` TIMESTAMP NULL DEFAULT NULL AFTER `reply`;
