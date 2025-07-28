/**
 * File Sharing Functionality for Milo Meet
 */

let currentUploadProgress = null;

// Initialize file sharing
document.addEventListener('DOMContentLoaded', function() {
    setupFileSharing();
    setupDragAndDrop();
    loadMeetingFiles();
});

function setupFileSharing() {
    // Files panel toggle
    const filesToggle = document.getElementById('filesToggle');
    if (filesToggle) {
        filesToggle.addEventListener('click', () => togglePanel('filesPanel'));
    }
    
    // File input change handler
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }
}

function setupDragAndDrop() {
    const dropzone = document.querySelector('.upload-dropzone');
    if (!dropzone) return;
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, unhighlight, false);
    });
    
    // Handle dropped files
    dropzone.addEventListener('drop', handleDrop, false);
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight(e) {
    e.currentTarget.classList.add('dragover');
}

function unhighlight(e) {
    e.currentTarget.classList.remove('dragover');
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    handleFiles(files);
}

function handleFileSelect(event) {
    const files = event.target.files;
    handleFiles(files);
    
    // Reset file input
    event.target.value = '';
}

function handleFiles(files) {
    [...files].forEach(uploadFile);
}

async function uploadFile(file) {
    // Validate file size
    const maxSize = 256 * 1024 * 1024; // 256MB
    if (file.size > maxSize) {
        showToast('File size exceeds 256MB limit', 'error');
        return;
    }
    
    // Show upload progress
    showUploadProgress(file);
    
    try {
        const response = await window.secureAPI.uploadFile(
            meetingConfig.meetingId, 
            file, 
            updateUploadProgress
        );
        
        if (response.success || response.data) {
            hideUploadProgress();
            showToast('File uploaded successfully!', 'success');
            
            // Add file to the list
            addFileToList(response.data || response);
            
            // Update file count
            updateFileCount();
            
            // Show system message in chat
            addSystemMessage(`📎 ${file.name} was shared`);
        } else {
            throw new Error('Upload failed');
        }
    } catch (error) {
        hideUploadProgress();
        console.error('File upload error:', error);
        showToast(`Failed to upload ${file.name}: ${error.message}`, 'error');
    }
}

function showUploadProgress(file) {
    hideUploadProgress(); // Hide any existing progress
    
    const progressHTML = `
        <div id="uploadProgress" class="upload-progress">
            <div class="upload-progress-text">
                <span class="upload-progress-filename">${file.name}</span>
                <span class="upload-progress-percent">0%</span>
            </div>
            <div class="upload-progress-bar">
                <div class="upload-progress-fill"></div>
            </div>
            <small>Uploading... Please wait</small>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', progressHTML);
    currentUploadProgress = document.getElementById('uploadProgress');
}

function updateUploadProgress(percent) {
    if (!currentUploadProgress) return;
    
    const fill = currentUploadProgress.querySelector('.upload-progress-fill');
    const percentText = currentUploadProgress.querySelector('.upload-progress-percent');
    
    if (fill && percentText) {
        fill.style.width = percent + '%';
        percentText.textContent = Math.round(percent) + '%';
    }
}

function hideUploadProgress() {
    if (currentUploadProgress) {
        currentUploadProgress.remove();
        currentUploadProgress = null;
    }
}

async function loadMeetingFiles() {
    try {
        const response = await window.secureAPI.getMeetingFiles(meetingConfig.meetingId);
        
        if (response.success || response.data) {
            const files = response.data?.files || response.files || [];
            
            const filesList = document.getElementById('filesList');
            if (filesList) {
                filesList.innerHTML = '';
                
                if (files.length === 0) {
                    filesList.innerHTML = '<p class="text-center text-muted">No files shared yet</p>';
                } else {
                    files.forEach(file => addFileToList(file, false));
                }
                
                updateFileCount(files.length);
            }
        }
    } catch (error) {
        console.error('Failed to load meeting files:', error);
    }
}

function addFileToList(file, animate = true) {
    const filesList = document.getElementById('filesList');
    if (!filesList) return;
    
    // Remove "no files" message if it exists
    const noFilesMsg = filesList.querySelector('.text-muted');
    if (noFilesMsg) {
        noFilesMsg.remove();
    }
    
    // Decrypt file data if encrypted
    const fileName = file.name && typeof file.name === 'string' && file.name.startsWith('ey') ? 
        decryptFileData(file.name) : (file.original_name || file.name);
    const uploaderName = file.uploader_name && typeof file.uploader_name === 'string' && file.uploader_name.startsWith('ey') ? 
        decryptFileData(file.uploader_name) : file.uploader_name;
    
    const fileExtension = fileName.split('.').pop().toLowerCase();
    const fileIcon = getFileIcon(fileExtension);
    const fileSize = file.size_formatted || formatFileSize(file.file_size || file.size);
    
    const fileHTML = `
        <div class="file-item" data-file-id="${file.id || file.file_id}">
            <div class="file-icon ${fileExtension}">
                <i class="fas ${fileIcon}"></i>
            </div>
            <div class="file-info">
                <div class="file-name" title="${fileName}">${fileName}</div>
                <div class="file-details">
                    <span>${fileSize}</span>
                    <span>by ${uploaderName || 'Unknown'}</span>
                    <span>${formatDate(file.uploaded_at)}</span>
                </div>
            </div>
            <div class="file-actions">
                <button class="btn-icon" onclick="downloadFile(${file.id || file.file_id})" title="Download">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        </div>
    `;
    
    if (animate) {
        filesList.insertAdjacentHTML('afterbegin', fileHTML);
        const newFileItem = filesList.firstElementChild;
        newFileItem.classList.add('slide-up');
    } else {
        filesList.insertAdjacentHTML('beforeend', fileHTML);
    }
}

function downloadFile(fileId) {
    try {
        window.secureAPI.downloadFile(fileId);
    } catch (error) {
        console.error('Download failed:', error);
        showToast('Failed to download file', 'error');
    }
}

function getFileIcon(extension) {
    const iconMap = {
        // Documents
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'xls': 'fa-file-excel',
        'xlsx': 'fa-file-excel',
        'ppt': 'fa-file-powerpoint',
        'pptx': 'fa-file-powerpoint',
        'txt': 'fa-file-alt',
        'csv': 'fa-file-csv',
        
        // Archives
        'zip': 'fa-file-archive',
        'rar': 'fa-file-archive',
        '7z': 'fa-file-archive',
        'tar': 'fa-file-archive',
        'gz': 'fa-file-archive',
        
        // Images
        'jpg': 'fa-file-image',
        'jpeg': 'fa-file-image',
        'png': 'fa-file-image',
        'gif': 'fa-file-image',
        'bmp': 'fa-file-image',
        'webp': 'fa-file-image',
        'svg': 'fa-file-image',
        
        // Videos
        'mp4': 'fa-file-video',
        'avi': 'fa-file-video',
        'mov': 'fa-file-video',
        'wmv': 'fa-file-video',
        'flv': 'fa-file-video',
        'mkv': 'fa-file-video',
        
        // Audio
        'mp3': 'fa-file-audio',
        'wav': 'fa-file-audio',
        'ogg': 'fa-file-audio',
        'flac': 'fa-file-audio',
        'aac': 'fa-file-audio'
    };
    
    return iconMap[extension] || 'fa-file';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = (now - date) / (1000 * 60 * 60);
    
    if (diffInHours < 1) {
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));
        return diffInMinutes <= 1 ? 'Just now' : `${diffInMinutes}m ago`;
    } else if (diffInHours < 24) {
        return `${Math.floor(diffInHours)}h ago`;
    } else {
        return date.toLocaleDateString();
    }
}

function updateFileCount(count = null) {
    const fileCountElement = document.getElementById('fileCount');
    if (!fileCountElement) return;
    
    if (count === null) {
        const fileItems = document.querySelectorAll('.file-item');
        count = fileItems.length;
    }
    
    fileCountElement.textContent = count;
}

function togglePanel(panelId) {
    // Hide all panels
    document.querySelectorAll('.panel').forEach(panel => {
        panel.classList.remove('active');
    });
    
    // Remove active class from all toggle buttons
    document.querySelectorAll('#participantsToggle, #chatToggle, #filesToggle').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected panel
    const panel = document.getElementById(panelId);
    if (panel) {
        panel.classList.add('active');
    }
    
    // Activate corresponding button
    const buttonMap = {
        'participantsPanel': 'participantsToggle',
        'chatPanel': 'chatToggle',
        'filesPanel': 'filesToggle'
    };
    
    const buttonId = buttonMap[panelId];
    if (buttonId) {
        const button = document.getElementById(buttonId);
        if (button) {
            button.classList.add('active');
        }
    }
}

function addSystemMessage(message) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    const messageHTML = `
        <div class="message system">
            <div class="message-content">${message}</div>
        </div>
    `;
    
    chatMessages.insertAdjacentHTML('beforeend', messageHTML);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Helper function to decrypt file data (placeholder - implement actual decryption)
function decryptFileData(encryptedData) {
    // This would use the same decryption as the secure API client
    // For now, return the data as-is
    return encryptedData;
}

// Export functions for use in other scripts
window.fileSharing = {
    togglePanel,
    downloadFile,
    loadMeetingFiles,
    updateFileCount
};