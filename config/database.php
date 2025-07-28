<?php
require_once __DIR__ . '/security.php';

class Database {
    private $host = 'localhost';
    private $db_name = 'milomeet';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }
        return $this->conn;
    }
}

// Global database connection
$database = new Database();
$pdo = $database->connect();

// Create tables if they don't exist
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id VARCHAR(20) UNIQUE NOT NULL,
    host_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Quick Meeting',
    password VARCHAR(255) DEFAULT NULL,
    max_participants INT DEFAULT 100,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scheduled_at TIMESTAMP NULL,
    FOREIGN KEY (host_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS meeting_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id VARCHAR(20) NOT NULL,
    user_id INT NULL,
    guest_name VARCHAR(100) NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_host BOOLEAN DEFAULT FALSE,
    is_muted BOOLEAN DEFAULT FALSE,
    video_enabled BOOLEAN DEFAULT TRUE,
    hand_raised BOOLEAN DEFAULT FALSE,
    is_kicked BOOLEAN DEFAULT FALSE,
    INDEX (meeting_id)
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id VARCHAR(20) NOT NULL,
    sender_id INT NULL,
    sender_name VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'emoji', 'system') DEFAULT 'text',
    is_broadcast BOOLEAN DEFAULT FALSE,
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (meeting_id)
);

CREATE TABLE IF NOT EXISTS meeting_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id VARCHAR(20) NOT NULL,
    user_id INT NULL,
    guest_name VARCHAR(100) NULL,
    can_chat BOOLEAN DEFAULT TRUE,
    can_share_screen BOOLEAN DEFAULT TRUE,
    can_unmute BOOLEAN DEFAULT TRUE,
    can_enable_video BOOLEAN DEFAULT TRUE,
    INDEX (meeting_id)
);

CREATE TABLE IF NOT EXISTS meeting_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id VARCHAR(20) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by INT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (meeting_id),
    INDEX (uploaded_by),
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS persistent_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    link_id VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cancelled_at TIMESTAMP NULL,
    INDEX (link_id),
    INDEX (user_id),
    INDEX (is_active),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS meeting_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(50) UNIQUE NOT NULL,
    persistent_link_id VARCHAR(50),
    meeting_id VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    INDEX (session_id),
    INDEX (persistent_link_id),
    INDEX (meeting_id),
    INDEX (is_active),
    FOREIGN KEY (persistent_link_id) REFERENCES persistent_links(link_id) ON DELETE CASCADE
);
";

$pdo->exec($sql);
?>