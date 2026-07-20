--
-- Ambassador Hub install SQL
-- Referential integrity between #__ambassadorhub_* tables is enforced in application code
-- (Table/Model classes), not via SQL FOREIGN KEY constraints -- this mirrors Joomla core's
-- own convention and keeps uninstall/migration simple across DB drivers.
--

CREATE TABLE IF NOT EXISTS `#__ambassadorhub_students` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `bio` TEXT NULL,
    `program` VARCHAR(255) NULL,
    `profile_picture` VARCHAR(255) NULL,
    `show_groups_on_profile` TINYINT(1) NOT NULL DEFAULT 1,
    `show_friends_on_profile` TINYINT(1) NOT NULL DEFAULT 1,
    `show_contributions_on_profile` TINYINT(1) NOT NULL DEFAULT 1,
    `created` DATETIME NOT NULL,
    `modified` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__ambassadorhub_groups` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `alias` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT NULL,
    `category` VARCHAR(150) NULL,
    `cover_image` VARCHAR(255) NULL,
    `profile_picture` VARCHAR(255) NULL,
    `manager_student_id` INT UNSIGNED NULL,
    `state` TINYINT(1) NOT NULL DEFAULT 1,
    `created` DATETIME NOT NULL,
    `modified` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_manager_student_id` (`manager_student_id`),
    KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__ambassadorhub_group_members` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `date_joined` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_group_student` (`group_id`, `student_id`),
    KEY `idx_student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__ambassadorhub_events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `alias` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT NULL,
    `event_date` DATETIME NOT NULL,
    `location` VARCHAR(255) NULL,
    `category` VARCHAR(150) NULL,
    `poster` VARCHAR(255) NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `group_id` INT UNSIGNED NOT NULL,
    `submitted_by` INT UNSIGNED NOT NULL,
    `approved_by` INT UNSIGNED NULL,
    `created` DATETIME NOT NULL,
    `modified` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_group_id` (`group_id`),
    KEY `idx_submitted_by` (`submitted_by`),
    KEY `idx_approved_by` (`approved_by`),
    KEY `idx_status` (`status`),
    KEY `idx_event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__ambassadorhub_event_attendees` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `date_rsvped` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_event_student` (`event_id`, `student_id`),
    KEY `idx_student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__ambassadorhub_posts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `content` TEXT NOT NULL,
    `group_id` INT UNSIGNED NOT NULL,
    `author_id` INT UNSIGNED NOT NULL,
    `media` VARCHAR(255) NULL,
    `created` DATETIME NOT NULL,
    `modified` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_group_id` (`group_id`),
    KEY `idx_author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__ambassadorhub_comments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `content` TEXT NOT NULL,
    `post_id` INT UNSIGNED NOT NULL,
    `author_id` INT UNSIGNED NOT NULL,
    `created` DATETIME NOT NULL,
    `modified` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_post_id` (`post_id`),
    KEY `idx_author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__ambassadorhub_friendships` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `requester_id` INT UNSIGNED NOT NULL,
    `addressee_id` INT UNSIGNED NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `created` DATETIME NOT NULL,
    `modified` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_requester_addressee` (`requester_id`, `addressee_id`),
    KEY `idx_addressee_id` (`addressee_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__ambassadorhub_group_proposals` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(150) NULL,
    `cover_image` VARCHAR(255) NULL,
    `profile_picture` VARCHAR(255) NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `submitted_by` INT UNSIGNED NOT NULL,
    `approved_by` INT UNSIGNED NULL,
    `created` DATETIME NOT NULL,
    `modified` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_submitted_by` (`submitted_by`),
    KEY `idx_approved_by` (`approved_by`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
