-- ============================================================
--  DhakaGrid (formerly GovConnect) — Database Schema
--  Reconstructed from the PHP source (consensus of CREATE/INSERT/UPDATE/SELECT statements)
--  Database: g1  (matches db_connect.php)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `g1`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `g1`;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. users — citizens, response teams and admins
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(100)  NOT NULL,
  `email`           VARCHAR(150)  NOT NULL,
  `phone`           VARCHAR(20)      NULL,
  `password`        VARCHAR(255)  NOT NULL,
  `role`            ENUM('user','response','admin') NOT NULL DEFAULT 'user',
  `status`          ENUM('pending','active','rejected') NOT NULL DEFAULT 'active',

  -- citizen profile
  `nid`             VARCHAR(50)       NULL,
  `dob`             DATE              NULL,
  `location`        VARCHAR(255)      NULL,
  `latitude`        DECIMAL(10,7)     NULL,
  `longitude`       DECIMAL(10,7)     NULL,
  `profile_pic`     VARCHAR(255)      NULL,

  -- response team profile
  `category`        VARCHAR(20)       NULL COMMENT 'police | fire | medical',
  `incharge_name`   VARCHAR(100)      NULL,
  `incharge_id`     VARCHAR(50)       NULL,
  `incharge_email`  VARCHAR(150)      NULL,
  `incharge_phone`  VARCHAR(20)       NULL,
  `identification`  VARCHAR(100)      NULL,
  `employee_number` VARCHAR(30)       NULL,
  `total_members`   INT UNSIGNED NOT NULL DEFAULT 0,
  `busy_members`    INT UNSIGNED NOT NULL DEFAULT 0,

  -- moderation
  `is_banned`       TINYINT(1)  NOT NULL DEFAULT 0,
  `ban_until`       VARCHAR(20)       NULL COMMENT "'permanent' or a datetime string",
  `ban_reason`      TEXT              NULL,

  `created_at`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY `uq_email` (`email`),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. problems — citizen reports & SOS alerts
-- ============================================================
DROP TABLE IF EXISTS `problems`;
CREATE TABLE `problems` (
  `problem_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED NOT NULL,
  `category`        VARCHAR(20)  NOT NULL DEFAULT 'other' COMMENT 'police|medical|fire|gov|other|SOS|traffic|water|waste',
  `description`     TEXT         NOT NULL,
  `suggestion`      VARCHAR(255)     NULL,
  `location`        VARCHAR(500)     NULL,
  `location_name`   VARCHAR(500)     NULL,
  `latitude`        DECIMAL(10,7)    NULL,
  `longitude`       DECIMAL(10,7)    NULL,
  `media_path`      VARCHAR(500)     NULL COMMENT 'comma-separated uploaded file names',

  `status`          ENUM('pending','verified','assigned','working','resolved','rejected') NOT NULL DEFAULT 'pending',
  `priority`        ENUM('low','medium','high','sos') NOT NULL DEFAULT 'medium',
  `assigned_to`     INT UNSIGNED     NULL,
  `working_members` INT UNSIGNED NOT NULL DEFAULT 0,
  `report`          TEXT             NULL,

  `deleted_by_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_by_team`  TINYINT(1) NOT NULL DEFAULT 0,

  `created_at`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`problem_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_problems_user`      FOREIGN KEY (`user_id`)     REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_problems_assigned`  FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. deleted_problems — archive of soft-deleted complaints
-- ============================================================
DROP TABLE IF EXISTS `deleted_problems`;
CREATE TABLE `deleted_problems` (
  `del_id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `problem_id`    INT UNSIGNED     NULL,
  `user_id`       INT UNSIGNED     NULL,
  `user_name`     VARCHAR(100)     NULL,
  `user_email`    VARCHAR(150)     NULL,
  `user_phone`    VARCHAR(50)      NULL,
  `category`      VARCHAR(50)      NULL,
  `description`   TEXT             NULL,
  `suggestion`    VARCHAR(255)     NULL,
  `location`      VARCHAR(255)     NULL,
  `status`        VARCHAR(50)      NULL,
  `priority`      VARCHAR(50)      NULL,
  `report`        TEXT             NULL,
  `deleted_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`del_id`),
  KEY `idx_problem_id` (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. unban_requests — banned-user appeals
--    (merged from the two CREATE variants in the codebase)
-- ============================================================
DROP TABLE IF EXISTS `unban_requests`;
CREATE TABLE `unban_requests` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `reason`         TEXT             NULL,
  `message`        TEXT             NULL,
  `status`         ENUM('pending','reviewed','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_response` TEXT             NULL,
  `reviewed_at`    DATETIME         NULL,
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_unban_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. warnings — admin warnings & public gov notices
-- ============================================================
DROP TABLE IF EXISTS `warnings`;
CREATE TABLE `warnings` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED     NULL COMMENT 'NULL = public broadcast',
  `admin_id`    INT UNSIGNED     NULL,
  `message`     TEXT             NULL,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. feedbacks — citizen rating on resolved complaints
-- ============================================================
DROP TABLE IF EXISTS `feedbacks`;
CREATE TABLE `feedbacks` (
  `feedback_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `problem_id`  INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED NOT NULL,
  `rating`      TINYINT UNSIGNED NOT NULL DEFAULT 5 COMMENT '1-5 stars',
  `comment`     TEXT             NULL,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`feedback_id`),
  UNIQUE KEY `uq_feedback` (`problem_id`, `user_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_feedback_problem` FOREIGN KEY (`problem_id`) REFERENCES `problems` (`problem_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feedback_user`    FOREIGN KEY (`user_id`)    REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. logs — activity / notification audit (SOS triggers etc.)
-- ============================================================
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `problem_id`        INT UNSIGNED     NULL,
  `user_id`           INT UNSIGNED     NULL,
  `notification_type` VARCHAR(50)      NULL,
  `message`           TEXT             NULL,
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_problem_id` (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;