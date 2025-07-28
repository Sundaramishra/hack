-- Milo Meet Database Structure
-- Complete SQL file for Google Meet-like platform
-- Created for PHP/MySQL implementation

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: milomeet
CREATE DATABASE IF NOT EXISTS `milomeet` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `milomeet`;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `meetings`
CREATE TABLE `meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` varchar(20) NOT NULL UNIQUE,
  `host_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Quick Meeting',
  `password` varchar(255) DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meeting_id` (`meeting_id`),
  KEY `idx_host_id` (`host_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`host_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `meeting_participants`
CREATE TABLE `meeting_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `left_at` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_host` tinyint(1) NOT NULL DEFAULT 0,
  `is_muted` tinyint(1) NOT NULL DEFAULT 0,
  `video_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `hand_raised` tinyint(1) NOT NULL DEFAULT 0,
  `is_kicked` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_meeting_id` (`meeting_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_joined_at` (`joined_at`),
  KEY `idx_is_host` (`is_host`),
  KEY `idx_last_activity` (`last_activity`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `messages`
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` varchar(20) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `message_type` enum('text','emoji','system') NOT NULL DEFAULT 'text',
  `is_broadcast` tinyint(1) NOT NULL DEFAULT 0,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_meeting_id` (`meeting_id`),
  KEY `idx_sender_id` (`sender_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_pinned` (`is_pinned`),
  KEY `idx_message_type` (`message_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `meeting_permissions`
CREATE TABLE `meeting_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `can_chat` tinyint(1) NOT NULL DEFAULT 1,
  `can_share_screen` tinyint(1) NOT NULL DEFAULT 1,
  `can_unmute` tinyint(1) NOT NULL DEFAULT 1,
  `can_enable_video` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_meeting_id` (`meeting_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `meeting_files`
CREATE TABLE `meeting_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` varchar(20) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_meeting_id` (`meeting_id`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  KEY `idx_uploaded_at` (`uploaded_at`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `persistent_links`
CREATE TABLE `persistent_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_id` varchar(50) NOT NULL UNIQUE,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `link_id` (`link_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `meeting_sessions`
CREATE TABLE `meeting_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(50) NOT NULL UNIQUE,
  `persistent_link_id` varchar(50) DEFAULT NULL,
  `meeting_id` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_persistent_link_id` (`persistent_link_id`),
  KEY `idx_meeting_id` (`meeting_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_started_at` (`started_at`),
  FOREIGN KEY (`persistent_link_id`) REFERENCES `persistent_links` (`link_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `api_rate_limit`
CREATE TABLE `api_rate_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(100) NOT NULL,
  `requests` int(11) NOT NULL DEFAULT 1,
  `window_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier` (`identifier`),
  KEY `idx_window_start` (`window_start`),
  KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `security_logs`
CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` json DEFAULT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'low',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_severity` (`severity`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `api_tokens`
CREATE TABLE `api_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_token_hash` (`token_hash`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_is_active` (`is_active`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Sample Data Insertion

-- Insert sample users (passwords are hashed using PHP's password_hash)
INSERT INTO `users` (`name`, `email`, `password`, `created_at`) VALUES
('Admin User', 'admin@milomeet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('Mike Johnson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('Sarah Wilson', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());

-- Insert sample meetings
INSERT INTO `meetings` (`meeting_id`, `host_id`, `title`, `is_active`, `created_at`) VALUES
('DEMO123456', 1, 'Demo Meeting Room', 1, NOW()),
('TEST789012', 2, 'Test Conference', 1, NOW()),
('SAMPLE3456', 3, 'Sample Presentation', 0, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Insert sample persistent links
INSERT INTO `persistent_links` (`link_id`, `user_id`, `title`, `description`, `created_at`) VALUES
('pl_demo123_1234567890', 1, 'Daily Standup Meeting', 'Regular team standup meeting every morning', NOW()),
('pl_test456_0987654321', 2, 'Client Consultation Room', 'Dedicated room for client meetings and consultations', NOW());

-- Insert sample meeting participants
INSERT INTO `meeting_participants` (`meeting_id`, `user_id`, `guest_name`, `is_host`, `joined_at`) VALUES
('DEMO123456', 1, NULL, 1, NOW()),
('DEMO123456', 2, NULL, 0, NOW()),
('TEST789012', 2, NULL, 1, NOW()),
('TEST789012', NULL, 'Guest User', 0, NOW());

-- Insert sample messages
INSERT INTO `messages` (`meeting_id`, `sender_id`, `sender_name`, `message`, `message_type`, `created_at`) VALUES
('DEMO123456', 1, 'Admin User', 'Welcome to the demo meeting!', 'text', NOW()),
('DEMO123456', 2, 'John Doe', 'Thanks for the invitation!', 'text', NOW()),
('DEMO123456', NULL, 'System', '📎 demo-file.pdf was shared by Admin User', 'system', NOW()),
('TEST789012', 2, 'John Doe', 'Let\'s start the presentation', 'text', NOW());

-- --------------------------------------------------------

-- Create indexes for better performance
CREATE INDEX idx_meetings_host_active ON meetings(host_id, is_active);
CREATE INDEX idx_participants_meeting_active ON meeting_participants(meeting_id, left_at);
CREATE INDEX idx_messages_meeting_time ON messages(meeting_id, created_at);
CREATE INDEX idx_files_meeting_time ON meeting_files(meeting_id, uploaded_at);
CREATE INDEX idx_persistent_user_active ON persistent_links(user_id, is_active);
CREATE INDEX idx_sessions_link_active ON meeting_sessions(persistent_link_id, is_active);

-- --------------------------------------------------------

-- Create views for common queries

-- View for active meetings with participant count
CREATE VIEW active_meetings_view AS
SELECT 
    m.meeting_id,
    m.title,
    m.host_id,
    u.name as host_name,
    m.created_at,
    COUNT(mp.id) as participant_count,
    COUNT(CASE WHEN mp.left_at IS NULL THEN 1 END) as active_participants
FROM meetings m
LEFT JOIN users u ON m.host_id = u.id
LEFT JOIN meeting_participants mp ON m.meeting_id = mp.meeting_id AND mp.is_kicked = 0
WHERE m.is_active = 1
GROUP BY m.id, m.meeting_id, m.title, m.host_id, u.name, m.created_at;

-- View for persistent links with session stats
CREATE VIEW persistent_links_stats AS
SELECT 
    pl.link_id,
    pl.title,
    pl.description,
    pl.user_id,
    u.name as owner_name,
    pl.created_at,
    pl.is_active,
    COUNT(ms.id) as total_sessions,
    COUNT(CASE WHEN ms.is_active = 1 THEN 1 END) as active_sessions
FROM persistent_links pl
LEFT JOIN users u ON pl.user_id = u.id
LEFT JOIN meeting_sessions ms ON pl.link_id = ms.persistent_link_id
GROUP BY pl.id, pl.link_id, pl.title, pl.description, pl.user_id, u.name, pl.created_at, pl.is_active;

-- --------------------------------------------------------

-- Create stored procedures

DELIMITER //

-- Procedure to clean up old data
CREATE PROCEDURE CleanupOldData()
BEGIN
    -- Clean up ended meetings older than 30 days
    DELETE FROM meetings 
    WHERE is_active = 0 AND ended_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Clean up old rate limit entries
    DELETE FROM api_rate_limit 
    WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 HOUR);
    
    -- Clean up old security logs (keep for 90 days)
    DELETE FROM security_logs 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    
    -- Clean up expired API tokens
    DELETE FROM api_tokens 
    WHERE expires_at < NOW() OR is_active = 0;
    
    -- Clean up old meeting sessions
    DELETE FROM meeting_sessions 
    WHERE is_active = 0 AND ended_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
END //

-- Procedure to end meeting and cleanup
CREATE PROCEDURE EndMeeting(IN meeting_id_param VARCHAR(20))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Mark meeting as inactive
    UPDATE meetings SET is_active = 0, ended_at = NOW() 
    WHERE meeting_id = meeting_id_param;
    
    -- Mark all participants as left
    UPDATE meeting_participants SET left_at = NOW() 
    WHERE meeting_id = meeting_id_param AND left_at IS NULL;
    
    -- End related meeting sessions
    UPDATE meeting_sessions SET is_active = 0, ended_at = NOW() 
    WHERE meeting_id = meeting_id_param AND is_active = 1;
    
    COMMIT;
END //

DELIMITER ;

-- --------------------------------------------------------

-- Create triggers

DELIMITER //

-- Trigger to update last_activity when participant performs actions
CREATE TRIGGER update_participant_activity 
BEFORE UPDATE ON meeting_participants
FOR EACH ROW
BEGIN
    IF NEW.is_muted != OLD.is_muted OR 
       NEW.video_enabled != OLD.video_enabled OR 
       NEW.hand_raised != OLD.hand_raised THEN
        SET NEW.last_activity = NOW();
    END IF;
END //

-- Trigger to log security events
CREATE TRIGGER log_failed_login_attempts
AFTER INSERT ON security_logs
FOR EACH ROW
BEGIN
    IF NEW.event_type = 'login_failed' AND NEW.severity = 'high' THEN
        -- Could trigger additional security measures here
        INSERT INTO security_logs (event_type, ip_address, details, severity) 
        VALUES ('multiple_failed_attempts', NEW.ip_address, 
                JSON_OBJECT('count', 1, 'timestamp', NOW()), 'critical');
    END IF;
END //

DELIMITER ;

-- --------------------------------------------------------

-- Set proper permissions and final configurations

-- Create database user (uncomment and modify as needed)
-- CREATE USER 'milomeet_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON milomeet.* TO 'milomeet_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Final optimizations
OPTIMIZE TABLE users, meetings, meeting_participants, messages, meeting_files, persistent_links, meeting_sessions;

COMMIT;

-- --------------------------------------------------------
-- End of SQL file
-- 
-- Database: milomeet
-- Tables: 11
-- Views: 2  
-- Procedures: 2
-- Triggers: 2
-- 
-- Features supported:
-- - User management with secure password hashing
-- - Meeting creation and management
-- - Participant tracking with real-time activity
-- - Chat system with pinned messages and emojis
-- - File sharing with automatic cleanup
-- - Persistent meeting links (max 2 per user)
-- - Advanced security with rate limiting and logging
-- - API token management
-- - Comprehensive indexing for performance
-- - Automated cleanup procedures
-- - Activity triggers and logging
-- 
-- Default login credentials (password: 'password'):
-- admin@milomeet.com / password
-- john@example.com / password  
-- jane@example.com / password
-- mike@example.com / password
-- sarah@example.com / password
-- --------------------------------------------------------