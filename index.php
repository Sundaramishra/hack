<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milo Meet - Home</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                <i class="fas fa-video"></i>
                <h1>Milo Meet</h1>
            </div>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </header>

        <main class="main-content">
            <div class="meeting-options">
                <div class="option-card">
                    <i class="fas fa-plus-circle"></i>
                    <h3>Create Meeting</h3>
                    <p>Start an instant meeting or schedule for later</p>
                    <button class="btn btn-primary" onclick="createMeeting()">New Meeting</button>
                </div>

                <div class="option-card">
                    <i class="fas fa-sign-in-alt"></i>
                    <h3>Join Meeting</h3>
                    <p>Join a meeting with a meeting ID</p>
                    <input type="text" id="meetingId" placeholder="Enter meeting ID" class="input-field">
                    <button class="btn btn-primary" onclick="joinMeeting()">Join</button>
                </div>

                <div class="option-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Schedule Meeting</h3>
                    <p>Schedule a meeting for later</p>
                    <button class="btn btn-primary" onclick="scheduleMeeting()">Schedule</button>
                </div>

                <div class="option-card">
                    <i class="fas fa-link"></i>
                    <h3>Persistent Links</h3>
                    <p>Create permanent meeting links (Max 2)</p>
                    <button class="btn btn-primary" onclick="showPersistentLinksModal()">Manage Links</button>
                </div>
            </div>

            <div class="recent-meetings">
                <h3>Recent Meetings</h3>
                <div id="recentMeetings">
                    <!-- Recent meetings will be loaded here -->
                </div>
            </div>
        </main>
    </div>

    <!-- Persistent Links Modal -->
    <div id="persistentLinksModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-link"></i> Persistent Links</h2>
                <span class="close" onclick="closeModal('persistentLinksModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="persistent-links-header">
                    <p>Create permanent meeting links that can be used multiple times. Maximum 2 links allowed.</p>
                    <button id="createPersistentLinkBtn" class="btn btn-primary" onclick="showCreatePersistentLinkForm()">
                        <i class="fas fa-plus"></i> Create New Link
                    </button>
                </div>
                
                <div id="createPersistentLinkForm" class="create-link-form hidden">
                    <h3>Create New Persistent Link</h3>
                    <div class="form-group">
                        <label for="persistentLinkTitle">Meeting Title *</label>
                        <input type="text" id="persistentLinkTitle" placeholder="Enter meeting title" maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label for="persistentLinkDescription">Description</label>
                        <textarea id="persistentLinkDescription" placeholder="Optional description" maxlength="500"></textarea>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-secondary" onclick="hideCreatePersistentLinkForm()">Cancel</button>
                        <button class="btn btn-primary" onclick="createPersistentLink()">Create Link</button>
                    </div>
                </div>

                <div id="persistentLinksList" class="persistent-links-list">
                    <!-- Persistent links will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/secure-api-client.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Set user data for secure API client
        document.body.dataset.userId = '<?php echo $user['id']; ?>';
    </script>
</body>
</html>