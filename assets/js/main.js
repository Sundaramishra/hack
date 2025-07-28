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

async function createMeeting() {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<div class="spinner"></div> Creating...';
    button.disabled = true;

    try {
        const response = await window.secureAPI.createMeeting({
            title: 'Quick Meeting',
            type: 'instant'
        });
        
        if (response.success || response.data) {
            const meetingId = response.data?.meetingId || response.meetingId;
            // Redirect to meeting
            window.location.href = `meeting.php?id=${meetingId}`;
        } else {
            showToast('Failed to create meeting', 'error');
        }
    } catch (error) {
        console.error('Error creating meeting:', error);
        showToast('Failed to create meeting. Please try again.', 'error');
    } finally {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

async function joinMeeting() {
    const meetingId = document.getElementById('meetingId').value.trim();
    
    if (!meetingId) {
        showToast('Please enter a meeting ID', 'error');
        return;
    }

    // Validate meeting ID format
    if (!/^[A-Z0-9]{10}$/.test(meetingId)) {
        showToast('Please enter a valid meeting ID (10 characters)', 'error');
        return;
    }

    try {
        const response = await window.secureAPI.checkMeeting(meetingId);
        
        if (response.data?.exists || response.exists) {
            window.location.href = `meeting.php?id=${meetingId}`;
        } else {
            showToast('Meeting not found or has ended', 'error');
        }
    } catch (error) {
        console.error('Error checking meeting:', error);
        showToast('Failed to check meeting. Please try again.', 'error');
    }
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

// Persistent Links Management
async function showPersistentLinksModal() {
    document.getElementById('persistentLinksModal').style.display = 'block';
    await loadPersistentLinks();
}

function showCreatePersistentLinkForm() {
    document.getElementById('createPersistentLinkForm').classList.remove('hidden');
    document.getElementById('createPersistentLinkBtn').style.display = 'none';
}

function hideCreatePersistentLinkForm() {
    document.getElementById('createPersistentLinkForm').classList.add('hidden');
    document.getElementById('createPersistentLinkBtn').style.display = 'block';
    
    // Clear form
    document.getElementById('persistentLinkTitle').value = '';
    document.getElementById('persistentLinkDescription').value = '';
}

async function createPersistentLink() {
    const title = document.getElementById('persistentLinkTitle').value.trim();
    const description = document.getElementById('persistentLinkDescription').value.trim();
    
    if (!title) {
        showToast('Please enter a meeting title', 'error');
        return;
    }
    
    try {
        const response = await window.secureAPI.createPersistentLink(title, description);
        
        if (response.success || response.data) {
            const linkData = response.data || response;
            showToast('Persistent link created successfully!', 'success');
            hideCreatePersistentLinkForm();
            await loadPersistentLinks();
            
            // Show the new link details
            showLinkCreatedDetails(linkData);
        } else {
            throw new Error(response.message || 'Failed to create persistent link');
        }
    } catch (error) {
        console.error('Create persistent link error:', error);
        showToast(`Failed to create link: ${error.message}`, 'error');
    }
}

async function loadPersistentLinks() {
    try {
        const response = await window.secureAPI.getPersistentLinks();
        
        if (response.success || response.data) {
            const data = response.data || response;
            displayPersistentLinks(data.links || [], data.can_create_more);
        } else {
            throw new Error('Failed to load persistent links');
        }
    } catch (error) {
        console.error('Load persistent links error:', error);
        document.getElementById('persistentLinksList').innerHTML = 
            '<p class="error-message">Failed to load persistent links</p>';
    }
}

function displayPersistentLinks(links, canCreateMore) {
    const container = document.getElementById('persistentLinksList');
    const createBtn = document.getElementById('createPersistentLinkBtn');
    
    // Update create button visibility
    if (!canCreateMore) {
        createBtn.style.display = 'none';
        const existingMsg = createBtn.parentNode.querySelector('.limit-message');
        if (!existingMsg) {
            createBtn.parentNode.insertAdjacentHTML('beforeend', 
                '<p class="limit-message"><i class="fas fa-info-circle"></i> Maximum 2 persistent links reached</p>');
        }
    } else {
        createBtn.style.display = 'block';
        const limitMsg = createBtn.parentNode.querySelector('.limit-message');
        if (limitMsg) limitMsg.remove();
    }
    
    if (links.length === 0) {
        container.innerHTML = '<p class="no-links-message">No persistent links created yet</p>';
        return;
    }
    
    const linksHTML = links.map(link => {
        // Decrypt data if needed (placeholder for now)
        const title = link.title;
        const description = link.description;
        
        return `
            <div class="persistent-link-item" data-link-id="${link.link_id}">
                <div class="link-info">
                    <div class="link-title">${escapeHtml(title)}</div>
                    ${description ? `<div class="link-description">${escapeHtml(description)}</div>` : ''}
                    <div class="link-stats">
                        <span><i class="fas fa-users"></i> ${link.active_sessions} active</span>
                        <span><i class="fas fa-history"></i> ${link.total_sessions} total sessions</span>
                        <span><i class="fas fa-calendar"></i> Created ${formatDate(link.created_at)}</span>
                    </div>
                    <div class="link-url">
                        <i class="fas fa-link"></i>
                        <span class="url-text">${link.full_url}</span>
                        <button class="btn-icon copy-btn" onclick="copyLinkToClipboard('${link.full_url}')" title="Copy Link">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="link-actions">
                    <button class="btn btn-secondary" onclick="openPersistentLink('${link.full_url}')" title="Open Link">
                        <i class="fas fa-external-link-alt"></i>
                    </button>
                    <button class="btn btn-danger" onclick="cancelPersistentLink('${link.link_id}', '${escapeHtml(title)}')" title="Cancel Link">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = linksHTML;
}

async function cancelPersistentLink(linkId, title) {
    if (!confirm(`Are you sure you want to cancel the persistent link "${title}"?\n\nThis will end all active sessions and make the link unusable.`)) {
        return;
    }
    
    try {
        const response = await window.secureAPI.cancelPersistentLink(linkId);
        
        if (response.success || response.data) {
            const data = response.data || response;
            showToast(`Persistent link cancelled successfully!${data.active_sessions_ended > 0 ? ` ${data.active_sessions_ended} active sessions ended.` : ''}`, 'success');
            await loadPersistentLinks();
        } else {
            throw new Error(response.message || 'Failed to cancel persistent link');
        }
    } catch (error) {
        console.error('Cancel persistent link error:', error);
        showToast(`Failed to cancel link: ${error.message}`, 'error');
    }
}

function copyLinkToClipboard(url) {
    navigator.clipboard.writeText(url).then(() => {
        showToast('Link copied to clipboard!', 'success');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('Link copied to clipboard!', 'success');
    });
}

function openPersistentLink(url) {
    window.open(url, '_blank');
}

function showLinkCreatedDetails(linkData) {
    const detailsHTML = `
        <div class="link-created-details">
            <h4><i class="fas fa-check-circle"></i> Persistent Link Created!</h4>
            <div class="new-link-info">
                <p><strong>Title:</strong> ${escapeHtml(linkData.title)}</p>
                <div class="new-link-url">
                    <p><strong>Link:</strong></p>
                    <div class="url-container">
                        <span class="url-text">${linkData.full_url}</span>
                        <button class="btn-icon" onclick="copyLinkToClipboard('${linkData.full_url}')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <p class="link-note"><i class="fas fa-info-circle"></i> This link can be used multiple times and will create new meeting sessions each time someone joins.</p>
            </div>
        </div>
    `;
    
    // Show in a temporary toast-like notification
    const notification = document.createElement('div');
    notification.className = 'link-created-notification';
    notification.innerHTML = detailsHTML;
    document.body.appendChild(notification);
    
    // Auto remove after 10 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 10000);
}