/**
 * Secure API Client for Milo Meet
 * Handles encrypted communication with backend APIs
 */
class SecureAPIClient {
    constructor() {
        this.apiToken = null;
        this.csrfToken = null;
        this.encryptionKey = null;
        this.baseURL = window.location.origin + '/api/';
        
        // Initialize secure client
        this.init();
    }
    
    async init() {
        // Get CSRF token from server
        await this.refreshCSRFToken();
        
        // Get API token if user is logged in
        if (this.isUserLoggedIn()) {
            await this.refreshAPIToken();
        }
    }
    
    isUserLoggedIn() {
        // Check if user session exists
        return document.body.dataset.userId !== undefined;
    }
    
    async refreshCSRFToken() {
        try {
            const response = await fetch('/api/get_csrf_token.php', {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                this.csrfToken = data.csrf_token;
            }
        } catch (error) {
            console.error('Failed to get CSRF token:', error);
        }
    }
    
    async refreshAPIToken() {
        try {
            const response = await fetch('/api/get_api_token.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                this.apiToken = data.api_token;
            }
        } catch (error) {
            console.error('Failed to get API token:', error);
        }
    }
    
    // Simple encryption for client-side (for demo - use proper crypto library in production)
    async encryptData(data) {
        const jsonString = JSON.stringify(data);
        const encoder = new TextEncoder();
        const dataBuffer = encoder.encode(jsonString);
        
        // Generate a random key for this request
        const key = await window.crypto.subtle.generateKey(
            { name: 'AES-GCM', length: 256 },
            true,
            ['encrypt', 'decrypt']
        );
        
        // Generate random IV
        const iv = window.crypto.getRandomValues(new Uint8Array(12));
        
        // Encrypt the data
        const encryptedBuffer = await window.crypto.subtle.encrypt(
            { name: 'AES-GCM', iv: iv },
            key,
            dataBuffer
        );
        
        // Export the key
        const exportedKey = await window.crypto.subtle.exportKey('raw', key);
        
        // Combine IV, key, and encrypted data
        const combined = new Uint8Array(
            iv.byteLength + exportedKey.byteLength + encryptedBuffer.byteLength
        );
        combined.set(iv, 0);
        combined.set(new Uint8Array(exportedKey), iv.byteLength);
        combined.set(new Uint8Array(encryptedBuffer), iv.byteLength + exportedKey.byteLength);
        
        // Convert to base64
        return btoa(String.fromCharCode(...combined));
    }
    
    async decryptData(encryptedData) {
        try {
            // Convert from base64
            const combined = new Uint8Array(
                atob(encryptedData).split('').map(char => char.charCodeAt(0))
            );
            
            // Extract IV, key, and encrypted data
            const iv = combined.slice(0, 12);
            const keyData = combined.slice(12, 44);
            const encrypted = combined.slice(44);
            
            // Import the key
            const key = await window.crypto.subtle.importKey(
                'raw',
                keyData,
                { name: 'AES-GCM' },
                false,
                ['decrypt']
            );
            
            // Decrypt the data
            const decryptedBuffer = await window.crypto.subtle.decrypt(
                { name: 'AES-GCM', iv: iv },
                key,
                encrypted
            );
            
            // Convert back to string and parse JSON
            const decoder = new TextDecoder();
            const jsonString = decoder.decode(decryptedBuffer);
            return JSON.parse(jsonString);
        } catch (error) {
            console.error('Decryption failed:', error);
            throw new Error('Failed to decrypt response data');
        }
    }
    
    generateRequestSignature(data, timestamp) {
        // Simple HMAC-like signature (use proper HMAC in production)
        const message = data + timestamp + 'MiloMeetSecretSalt';
        return btoa(message).replace(/[^a-zA-Z0-9]/g, '').substring(0, 64);
    }
    
    async makeSecureRequest(endpoint, options = {}) {
        const timestamp = Math.floor(Date.now() / 1000);
        let requestData = options.data || {};
        
        // Encrypt request data if needed
        if (options.encrypt !== false && Object.keys(requestData).length > 0) {
            const encryptedData = await this.encryptData(requestData);
            requestData = {
                encrypted: true,
                data: encryptedData
            };
        }
        
        const requestBody = JSON.stringify(requestData);
        const signature = this.generateRequestSignature(requestBody, timestamp);
        
        const headers = {
            'Content-Type': 'application/json',
            'X-Request-Time': timestamp.toString(),
            'X-API-Signature': signature,
            ...options.headers
        };
        
        // Add authentication headers if available
        if (this.apiToken) {
            headers['X-API-Token'] = this.apiToken;
        }
        
        if (this.csrfToken) {
            headers['X-CSRF-Token'] = this.csrfToken;
        }
        
        try {
            const response = await fetch(this.baseURL + endpoint, {
                method: options.method || 'POST',
                headers: headers,
                body: options.method === 'GET' ? undefined : requestBody,
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || `HTTP ${response.status}`);
            }
            
            const responseData = await response.json();
            
            // Verify response signature if present
            const responseSignature = response.headers.get('X-Response-Signature');
            const responseTime = response.headers.get('X-Response-Time');
            
            if (responseSignature && responseTime) {
                const expectedSignature = this.generateRequestSignature(
                    JSON.stringify(responseData), 
                    responseTime
                );
                
                if (responseSignature !== expectedSignature) {
                    console.warn('Response signature verification failed');
                }
            }
            
            // Decrypt response data if encrypted
            if (responseData.encrypted && responseData.data) {
                try {
                    const decryptedData = await this.decryptData(responseData.data);
                    return {
                        success: true,
                        data: decryptedData,
                        timestamp: responseData.timestamp
                    };
                } catch (error) {
                    console.error('Failed to decrypt response:', error);
                    throw new Error('Failed to decrypt server response');
                }
            }
            
            return responseData;
        } catch (error) {
            console.error('API request failed:', error);
            
            // Handle token expiration
            if (error.message.includes('401') || error.message.includes('token')) {
                await this.refreshAPIToken();
            }
            
            throw error;
        }
    }
    
    // Convenience methods for common operations
    async createMeeting(meetingData) {
        return this.makeSecureRequest('create_meeting.php', {
            method: 'POST',
            data: meetingData
        });
    }
    
    async checkMeeting(meetingId) {
        return this.makeSecureRequest(`check_meeting.php?id=${meetingId}`, {
            method: 'GET',
            encrypt: false // Public endpoint
        });
    }
    
    async getRecentMeetings() {
        return this.makeSecureRequest('get_recent_meetings.php', {
            method: 'GET'
        });
    }
    
    async joinMeeting(meetingId, guestName = null) {
        return this.makeSecureRequest('join_meeting.php', {
            method: 'POST',
            data: { meeting_id: meetingId, guest_name: guestName }
        });
    }
    
    async sendMessage(meetingId, message, messageType = 'text') {
        return this.makeSecureRequest('send_message.php', {
            method: 'POST',
            data: { 
                meeting_id: meetingId, 
                message: message, 
                message_type: messageType 
            }
        });
    }
    
    async getMeetingParticipants(meetingId) {
        return this.makeSecureRequest('get_participants.php', {
            method: 'POST',
            data: { meeting_id: meetingId }
        });
    }
    
    async updateParticipantStatus(meetingId, action, targetUserId = null) {
        return this.makeSecureRequest('update_participant.php', {
            method: 'POST',
            data: { 
                meeting_id: meetingId, 
                action: action, 
                target_user_id: targetUserId 
            }
        });
    }
    
    // Security monitoring
    logSecurityEvent(event, details = {}) {
        // Log security events to server
        this.makeSecureRequest('log_security_event.php', {
            method: 'POST',
            data: { event: event, details: details }
        }).catch(error => {
            console.error('Failed to log security event:', error);
        });
    }
    
    // Rate limiting check
    isRateLimited() {
        const lastRequest = localStorage.getItem('last_api_request');
        const now = Date.now();
        
        if (lastRequest && (now - parseInt(lastRequest)) < 100) { // 100ms minimum between requests
            return true;
        }
        
        localStorage.setItem('last_api_request', now.toString());
        return false;
    }
    
    // Input sanitization
    sanitizeInput(input) {
        if (typeof input === 'string') {
            return input.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                       .replace(/[<>]/g, '');
        }
        
        if (typeof input === 'object' && input !== null) {
            const sanitized = {};
            for (const key in input) {
                if (input.hasOwnProperty(key)) {
                    sanitized[key] = this.sanitizeInput(input[key]);
                }
            }
            return sanitized;
        }
        
        return input;
    }
}

// Global instance
window.secureAPI = new SecureAPIClient();

// Error handling for failed API calls
window.addEventListener('unhandledrejection', function(event) {
    if (event.reason && event.reason.message) {
        const message = event.reason.message;
        
        if (message.includes('Rate limit exceeded')) {
            showToast('Too many requests. Please slow down.', 'error');
            event.preventDefault();
        } else if (message.includes('Authentication required')) {
            showToast('Session expired. Please login again.', 'error');
            setTimeout(() => {
                window.location.href = '/login.php';
            }, 2000);
            event.preventDefault();
        } else if (message.includes('decrypt')) {
            showToast('Security error. Please refresh the page.', 'error');
            event.preventDefault();
        }
    }
});

// Periodic token refresh
setInterval(async () => {
    if (window.secureAPI && window.secureAPI.isUserLoggedIn()) {
        try {
            await window.secureAPI.refreshAPIToken();
        } catch (error) {
            console.error('Token refresh failed:', error);
        }
    }
}, 30 * 60 * 1000); // Refresh every 30 minutes

// Security monitoring
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        // User switched tabs/minimized - log for security monitoring
        window.secureAPI?.logSecurityEvent('tab_hidden', {
            timestamp: Date.now()
        });
    }
});

// Detect potential XSS attempts
const originalInnerHTML = Element.prototype.innerHTML;
Element.prototype.innerHTML = function(value) {
    if (typeof value === 'string' && /<script/i.test(value)) {
        window.secureAPI?.logSecurityEvent('xss_attempt_detected', {
            element: this.tagName,
            content: value.substring(0, 100)
        });
        console.warn('Potential XSS attempt blocked');
        return;
    }
    return originalInnerHTML.call(this, value);
};