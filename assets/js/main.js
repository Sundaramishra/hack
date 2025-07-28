// Main JavaScript for Milo Meet Homepage

document.addEventListener('DOMContentLoaded', function() {
    loadRecentMeetings();
    setupEventListeners();
});

function setupEventListeners() {
    // Enter key support for meeting ID input
    const meetingIdInput = document.getElementById('meetingId');
    if (meetingIdInput) {
        meetingIdInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                joinMeeting();
            }
        });
    }
}

function createMeeting() {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<div class="spinner"></div> Creating...';
    button.disabled = true;

    // Create meeting via AJAX
    fetch('api/create_meeting.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            title: 'Quick Meeting',
            type: 'instant'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to meeting
            window.location.href = `meeting.php?id=${data.meetingId}`;
        } else {
            alert('Failed to create meeting: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create meeting. Please try again.');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function joinMeeting() {
    const meetingId = document.getElementById('meetingId').value.trim();
    
    if (!meetingId) {
        alert('Please enter a meeting ID');
        return;
    }

    // Validate meeting ID format
    if (!/^[A-Z0-9]{10}$/.test(meetingId)) {
        alert('Please enter a valid meeting ID (10 characters)');
        return;
    }

    // Check if meeting exists
    fetch(`api/check_meeting.php?id=${meetingId}`)
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            window.location.href = `meeting.php?id=${meetingId}`;
        } else {
            alert('Meeting not found or has ended');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to check meeting. Please try again.');
    });
}

function scheduleMeeting() {
    // Open schedule meeting modal
    showScheduleModal();
}

function showScheduleModal() {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Schedule Meeting</h3>
                <button class="modal-close" onclick="closeModal(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <div class="form-group">
                        <label for="meetingTitle">Meeting Title</label>
                        <input type="text" id="meetingTitle" name="title" value="Scheduled Meeting" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="meetingDate">Date</label>
                        <input type="date" id="meetingDate" name="date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="meetingTime">Time</label>
                        <input type="time" id="meetingTime" name="time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="meetingPassword">Password (Optional)</label>
                        <input type="password" id="meetingPassword" name="password" placeholder="Leave empty for no password">
                    </div>
                    
                    <div class="form-group">
                        <label for="maxParticipants">Max Participants</label>
                        <select id="maxParticipants" name="max_participants">
                            <option value="25">25 participants</option>
                            <option value="50">50 participants</option>
                            <option value="100" selected>100 participants</option>
                            <option value="250">250 participants</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full">Schedule Meeting</button>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('meetingDate').min = today;
    
    // Handle form submission
    document.getElementById('scheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        scheduleNewMeeting(new FormData(this));
    });
    
    // Add fade in animation
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function scheduleNewMeeting(formData) {
    const submitBtn = document.querySelector('#scheduleForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="spinner"></div> Scheduling...';
    submitBtn.disabled = true;

    fetch('api/schedule_meeting.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal(document.querySelector('.modal'));
            alert(`Meeting scheduled successfully!\nMeeting ID: ${data.meetingId}\nJoin URL: ${window.location.origin}/meeting.php?id=${data.meetingId}`);
            loadRecentMeetings(); // Refresh the list
        } else {
            alert('Failed to schedule meeting: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to schedule meeting. Please try again.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function loadRecentMeetings() {
    fetch('api/get_recent_meetings.php')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('recentMeetings');
        
        if (data.success && data.meetings.length > 0) {
            container.innerHTML = data.meetings.map(meeting => `
                <div class="meeting-item">
                    <div class="meeting-info">
                        <h4>${escapeHtml(meeting.title)}</h4>
                        <p>ID: ${meeting.meeting_id}</p>
                        <p>Created: ${formatDate(meeting.created_at)}</p>
                        ${meeting.scheduled_at ? `<p>Scheduled: ${formatDate(meeting.scheduled_at)}</p>` : ''}
                    </div>
                    <div class="meeting-actions">
                        <button class="btn btn-primary" onclick="joinMeetingById('${meeting.meeting_id}')">
                            <i class="fas fa-sign-in-alt"></i> Join
                        </button>
                        <button class="btn btn-secondary" onclick="copyMeetingLink('${meeting.meeting_id}')">
                            <i class="fas fa-copy"></i> Copy Link
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-center text-muted">No recent meetings</p>';
        }
    })
    .catch(error => {
        console.error('Error loading recent meetings:', error);
        document.getElementById('recentMeetings').innerHTML = '<p class="text-center text-error">Failed to load recent meetings</p>';
    });
}

function joinMeetingById(meetingId) {
    window.location.href = `meeting.php?id=${meetingId}`;
}

function copyMeetingLink(meetingId) {
    const link = `${window.location.origin}/meeting.php?id=${meetingId}`;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(link).then(() => {
            showToast('Meeting link copied to clipboard!');
        }).catch(() => {
            fallbackCopyTextToClipboard(link);
        });
    } else {
        fallbackCopyTextToClipboard(link);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('Meeting link copied to clipboard!');
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
        alert('Failed to copy link. Please copy manually: ' + text);
    }
    
    document.body.removeChild(textArea);
}

function closeModal(element) {
    const modal = element.closest('.modal');
    if (modal) {
        modal.remove();
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Add styles if not already added
    if (!document.getElementById('toast-styles')) {
        const styles = document.createElement('style');
        styles.id = 'toast-styles';
        styles.textContent = `
            .toast {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                padding: 15px 20px;
                border-radius: 12px;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 10000;
                animation: slideInRight 0.3s ease-out;
                border-left: 4px solid var(--success);
            }
            
            .toast-error {
                border-left-color: var(--error);
            }
            
            .toast i {
                color: var(--success);
            }
            
            .toast-error i {
                color: var(--error);
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(styles);
    }
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease-out reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Utility functions
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// Add meeting item styles
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('meeting-item-styles')) {
        const styles = document.createElement('style');
        styles.id = 'meeting-item-styles';
        styles.textContent = `
            .meeting-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px;
                background: var(--light-gray);
                border-radius: var(--border-radius);
                margin-bottom: 10px;
                transition: var(--transition);
            }
            
            .meeting-item:hover {
                background: var(--light-blue);
            }
            
            .meeting-info h4 {
                margin: 0 0 5px 0;
                color: var(--dark-gray);
                font-size: 1rem;
            }
            
            .meeting-info p {
                margin: 2px 0;
                color: var(--medium-gray);
                font-size: 0.85rem;
            }
            
            .meeting-actions {
                display: flex;
                gap: 10px;
            }
            
            .meeting-actions .btn {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
            
            @media (max-width: 768px) {
                .meeting-item {
                    flex-direction: column;
                    gap: 15px;
                    text-align: center;
                }
                
                .meeting-actions {
                    width: 100%;
                    justify-content: center;
                }
            }
        `;
        document.head.appendChild(styles);
    }
});