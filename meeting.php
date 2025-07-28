<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$meetingId = $_GET['id'] ?? '';
$guestName = $_GET['guest'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

if (!$meetingId) {
    header('Location: index.php');
    exit();
}

// Get meeting details
$meeting = getMeetingById($meetingId);
if (!$meeting) {
    die('Meeting not found or has ended');
}

// Handle guest or user join
if ($guestName && !$userId) {
    // Guest joining
    if (!joinMeeting($meetingId, null, $guestName)) {
        die('Failed to join meeting');
    }
    $currentUser = ['name' => $guestName, 'is_guest' => true];
} elseif ($userId) {
    // Registered user joining
    if (!joinMeeting($meetingId, $userId, null)) {
        die('Failed to join meeting');
    }
    $currentUser = getUserById($userId);
    $currentUser['is_guest'] = false;
} else {
    header('Location: login.php');
    exit();
}

$isHost = ($userId && $userId == $meeting['host_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milo Meet - <?php echo htmlspecialchars($meeting['title']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/meeting.css" rel="stylesheet">
</head>
<body class="meeting-page">
    <div id="meetingContainer" class="meeting-container">
        <!-- Meeting Header -->
        <header class="meeting-header">
            <div class="meeting-info">
                <h2><?php echo htmlspecialchars($meeting['title']); ?></h2>
                <span class="meeting-id">ID: <?php echo htmlspecialchars($meetingId); ?></span>
            </div>
            <div class="meeting-time">
                <i class="fas fa-clock"></i>
                <span id="meetingTime">00:00</span>
            </div>
            <?php if ($isHost): ?>
            <div class="host-controls">
                <button class="btn btn-secondary" onclick="endMeeting()">
                    <i class="fas fa-sign-out-alt"></i> End Meeting
                </button>
            </div>
            <?php endif; ?>
        </header>

        <!-- Main Meeting Area -->
        <main class="meeting-main">
            <!-- Video Grid -->
            <div class="video-section">
                <div id="videoGrid" class="video-grid">
                    <!-- Main video -->
                    <div class="video-container main-video">
                        <video id="localVideo" autoplay muted playsinline></video>
                        <div class="video-overlay">
                            <span class="participant-name"><?php echo htmlspecialchars($currentUser['name']); ?> (You)</span>
                            <div class="video-controls">
                                <button id="toggleVideo" class="control-btn">
                                    <i class="fas fa-video"></i>
                                </button>
                                <button id="toggleAudio" class="control-btn">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Remote videos will be added here dynamically -->
                </div>

                <!-- Screen Share Area -->
                <div id="screenShareArea" class="screen-share-area hidden">
                    <div class="screen-share-container">
                        <video id="screenShareVideo" autoplay playsinline></video>
                        <div class="screen-share-overlay">
                            <span id="screenShareName">Screen Share</span>
                            <button id="stopScreenShare" class="btn btn-secondary hidden">
                                <i class="fas fa-stop"></i> Stop Sharing
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div id="sidebar" class="sidebar">
                <!-- Participants Panel -->
                <div id="participantsPanel" class="panel active">
                    <div class="panel-header">
                        <h3><i class="fas fa-users"></i> Participants</h3>
                        <span id="participantCount">1</span>
                    </div>
                    <div class="panel-content">
                        <div id="participantsList" class="participants-list">
                            <!-- Participants will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Chat Panel -->
                <div id="chatPanel" class="panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-comments"></i> Chat</h3>
                        <div class="chat-options">
                            <?php if ($isHost): ?>
                            <button class="btn-icon" onclick="toggleBroadcastMode()" title="Broadcast Mode">
                                <i class="fas fa-bullhorn"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="panel-content">
                        <div id="pinnedMessages" class="pinned-messages hidden">
                            <!-- Pinned messages will appear here -->
                        </div>
                        <div id="chatMessages" class="chat-messages">
                            <!-- Chat messages will appear here -->
                        </div>
                        <div class="chat-input">
                            <div class="emoji-picker">
                                <button class="emoji-btn" onclick="toggleEmojiPicker()">😊</button>
                                <div id="emojiList" class="emoji-list hidden">
                                    <span onclick="insertEmoji('👋')">👋</span>
                                    <span onclick="insertEmoji('👍')">👍</span>
                                    <span onclick="insertEmoji('👎')">👎</span>
                                    <span onclick="insertEmoji('❤️')">❤️</span>
                                    <span onclick="insertEmoji('😂')">😂</span>
                                    <span onclick="insertEmoji('😮')">😮</span>
                                    <span onclick="insertEmoji('🎉')">🎉</span>
                                    <span onclick="insertEmoji('🔥')">🔥</span>
                                </div>
                            </div>
                            <input type="text" id="chatInput" placeholder="Type a message..." maxlength="500">
                            <button id="sendMessage" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Meeting Controls -->
        <div class="meeting-controls">
            <div class="control-group">
                <button id="micButton" class="control-btn primary">
                    <i class="fas fa-microphone"></i>
                </button>
                <button id="videoButton" class="control-btn primary">
                    <i class="fas fa-video"></i>
                </button>
            </div>

            <div class="control-group">
                <button id="screenShareButton" class="control-btn">
                    <i class="fas fa-desktop"></i>
                    <span>Share</span>
                </button>
                <button id="handRaiseButton" class="control-btn">
                    <i class="fas fa-hand-paper"></i>
                    <span>Raise Hand</span>
                </button>
                <button id="chatToggle" class="control-btn">
                    <i class="fas fa-comments"></i>
                    <span>Chat</span>
                </button>
                <button id="participantsToggle" class="control-btn active">
                    <i class="fas fa-users"></i>
                    <span>People</span>
                </button>
            </div>

            <div class="control-group">
                <button id="settingsButton" class="control-btn">
                    <i class="fas fa-cog"></i>
                </button>
                <button id="leaveButton" class="control-btn danger">
                    <i class="fas fa-phone-slash"></i>
                    <span>Leave</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Settings Modal -->
    <div id="settingsModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Settings</h3>
                <button class="modal-close" onclick="closeModal('settingsModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="settings-group">
                    <h4>Audio</h4>
                    <div class="setting-item">
                        <label>Microphone</label>
                        <select id="microphoneSelect">
                            <option>Default Microphone</option>
                        </select>
                    </div>
                    <div class="setting-item">
                        <label>Speaker</label>
                        <select id="speakerSelect">
                            <option>Default Speaker</option>
                        </select>
                    </div>
                </div>
                <div class="settings-group">
                    <h4>Video</h4>
                    <div class="setting-item">
                        <label>Camera</label>
                        <select id="cameraSelect">
                            <option>Default Camera</option>
                        </select>
                    </div>
                    <div class="setting-item">
                        <label>Video Quality</label>
                        <select id="qualitySelect">
                            <option value="720p">720p HD</option>
                            <option value="480p">480p</option>
                            <option value="360p">360p</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Screen Share Options Modal -->
    <div id="screenShareModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Share your screen</h3>
                <button class="modal-close" onclick="closeModal('screenShareModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="share-options">
                    <button class="share-option" onclick="shareScreen('screen')">
                        <i class="fas fa-desktop"></i>
                        <span>Entire Screen</span>
                    </button>
                    <button class="share-option" onclick="shareScreen('window')">
                        <i class="fas fa-window-maximize"></i>
                        <span>Application Window</span>
                    </button>
                    <button class="share-option" onclick="shareScreen('tab')">
                        <i class="fas fa-chrome"></i>
                        <span>Browser Tab</span>
                    </button>
                </div>
                <div class="share-audio">
                    <label>
                        <input type="checkbox" id="shareAudio">
                        Share audio
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Participant Context Menu -->
    <div id="participantMenu" class="context-menu hidden">
        <?php if ($isHost): ?>
        <button onclick="muteParticipant()">
            <i class="fas fa-microphone-slash"></i> Mute
        </button>
        <button onclick="removeParticipant()">
            <i class="fas fa-user-times"></i> Remove
        </button>
        <button onclick="changePermissions()">
            <i class="fas fa-user-cog"></i> Permissions
        </button>
        <?php endif; ?>
        <button onclick="sendPrivateMessage()">
            <i class="fas fa-envelope"></i> Private Message
        </button>
    </div>

    <script>
        // Meeting configuration
        const meetingConfig = {
            meetingId: '<?php echo $meetingId; ?>',
            userId: <?php echo $userId ? $userId : 'null'; ?>,
            userName: '<?php echo addslashes($currentUser['name']); ?>',
            isHost: <?php echo $isHost ? 'true' : 'false'; ?>,
            isGuest: <?php echo $currentUser['is_guest'] ? 'true' : 'false'; ?>
        };
    </script>
    <script src="assets/js/webrtc.js"></script>
    <script src="assets/js/meeting.js"></script>
    <script src="assets/js/chat.js"></script>
</body>
</html>