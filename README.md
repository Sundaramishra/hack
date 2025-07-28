# Milo Meet - Video Conferencing Platform

Milo Meet is a comprehensive video conferencing platform built with PHP that provides Google Meet-like functionality with a beautiful white and blue marble theme design.

## Features

### 🎥 Core Video Conferencing
- **HD Video Calls** - High-quality video with multiple participants
- **Audio Communication** - Crystal clear audio with noise suppression
- **Screen Sharing** - Share entire screen, application window, or browser tab
- **Screen Share with Audio** - Option to share system audio during screen sharing

### 👥 Meeting Management
- **Instant Meetings** - Create and join meetings immediately
- **Scheduled Meetings** - Schedule meetings for future dates
- **Meeting Links** - Generate shareable meeting links
- **Meeting IDs** - 10-character unique meeting identifiers
- **Password Protection** - Optional password protection for meetings
- **Participant Limits** - Configurable participant limits (25, 50, 100, 250)

### 🔐 User Management
- **User Registration** - Create accounts with strong password requirements
- **Guest Access** - Join meetings without creating an account
- **Password Security** - 8+ characters with uppercase, lowercase, numbers, and special characters
- **Secure Authentication** - Password hashing with bcrypt

### 💬 Communication Features
- **Real-time Chat** - Text messaging during meetings
- **Private Messages** - Person-to-person messaging
- **Broadcast Messages** - Host can send messages to all participants
- **Emoji Support** - Send emojis and reactions
- **Message Pinning** - Pin important messages for all participants
- **Message History** - View previous messages when rejoining

### 🎛️ Host Controls
- **Participant Management** - Mute, remove, or manage participant permissions
- **Meeting Controls** - End meetings, manage settings
- **Permission Management** - Control who can chat, share screen, unmute
- **Hand Raise Management** - See and manage raised hands
- **Broadcast Mode** - Send announcements to all participants

### 🙋 Participant Features
- **Hand Raising** - Raise hand to get attention
- **Mute/Unmute** - Control your own audio
- **Video On/Off** - Control your own video
- **Chat Participation** - Send messages and emojis
- **Screen Sharing** - Share your screen (if permitted)

### 📱 Responsive Design
- **Mobile Friendly** - Fully responsive design for all devices
- **Touch Optimized** - Touch-friendly controls for mobile devices
- **Adaptive Layout** - Layout adjusts based on screen size
- **Cross-browser Support** - Works on all modern browsers

### 🎨 Beautiful UI/UX
- **White & Blue Marble Theme** - Elegant marble-inspired design
- **Smooth Animations** - Fluid transitions and animations
- **Modern Interface** - Clean, intuitive user interface
- **Glass Morphism** - Modern glassmorphism effects with backdrop blur

## Installation

### Prerequisites
- **PHP 7.4+** with PDO MySQL extension
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Web Server** (Apache/Nginx)
- **HTTPS** (Required for WebRTC functionality)
- **Modern Browser** with WebRTC support

### Step 1: Download and Setup
```bash
# Clone or download the project
git clone <repository-url> milo-meet
cd milo-meet

# Set proper permissions
chmod 755 -R .
chmod 777 -R uploads/ (if you add file upload functionality later)
```

### Step 2: Database Setup
1. Create a MySQL database named `milomeet`:
```sql
CREATE DATABASE milomeet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update database configuration in `config/database.php`:
```php
private $host = 'localhost';        // Your database host
private $db_name = 'milomeet';      // Your database name
private $username = 'your_username'; // Your database username
private $password = 'your_password'; // Your database password
```

3. The database tables will be created automatically when you first access the application.

### Step 3: Web Server Configuration

#### Apache (.htaccess)
Create `.htaccess` file in the root directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data: https:; media-src 'self' blob:; connect-src 'self' wss: https:;"

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

#### Nginx
Add to your Nginx server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

# Security headers
add_header X-Content-Type-Options nosniff;
add_header X-Frame-Options DENY;
add_header X-XSS-Protection "1; mode=block";
add_header Referrer-Policy "strict-origin-when-cross-origin";
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data: https:; media-src 'self' blob:; connect-src 'self' wss: https:;";
```

### Step 4: SSL Certificate (Required)
WebRTC requires HTTPS. You can use:
- **Let's Encrypt** (Free): `certbot --apache` or `certbot --nginx`
- **Self-signed certificate** (Development only)
- **Cloudflare** (Free SSL proxy)

### Step 5: Test Installation
1. Navigate to your domain: `https://yourdomain.com`
2. Create a test account
3. Create a test meeting
4. Test video, audio, and screen sharing

## Configuration

### Password Requirements
The system enforces strong passwords with:
- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 lowercase letter  
- At least 1 number
- At least 1 special character (@$!%*?&)

### Meeting Settings
Default settings can be modified in `includes/functions.php`:
- Maximum participants per meeting
- Meeting duration limits
- Default video quality
- Chat message limits

### Security Settings
- Session timeout
- Password complexity
- Rate limiting (can be added)
- CORS settings

## Usage

### For Hosts
1. **Create Account**: Register with a valid email and strong password
2. **Create Meeting**: Click "New Meeting" for instant meeting or "Schedule" for future meetings
3. **Share Meeting**: Share the meeting ID or link with participants
4. **Manage Participants**: Use host controls to mute, remove, or manage permissions
5. **End Meeting**: Use "End Meeting" button to close the session

### For Participants
1. **Join with Account**: Login and join via meeting ID
2. **Join as Guest**: Enter meeting ID and your name (no account needed)
3. **Use Controls**: Mute/unmute, turn video on/off, raise hand, chat
4. **Share Screen**: Click share button and choose what to share
5. **Leave Meeting**: Click leave button to exit

### Chat Features
- **Send Messages**: Type in chat box and press Enter
- **Send Emojis**: Click emoji button to select reactions
- **Private Messages**: Right-click participant name (coming soon)
- **View Pinned**: Important messages stay visible at top

## Browser Support

### Fully Supported
- **Chrome 70+**
- **Firefox 70+**
- **Safari 12+**
- **Edge 79+**

### Mobile Support
- **Chrome Mobile 70+**
- **Safari iOS 12+**
- **Samsung Internet 10+**

## Troubleshooting

### Common Issues

**Camera/Microphone Not Working**
- Check browser permissions
- Ensure HTTPS is enabled
- Try refreshing the page
- Check if other apps are using camera/mic

**Screen Sharing Not Working**
- Ensure HTTPS is enabled
- Update browser to latest version
- Check browser screen sharing permissions
- Try different sharing options (screen/window/tab)

**Audio Issues**
- Check system volume levels
- Test with different browsers
- Verify microphone permissions
- Check for browser audio blocking

**Connection Issues**
- Verify internet connection
- Check firewall settings
- Ensure WebRTC is enabled in browser
- Try different network if possible

### Performance Optimization
- Use Chrome or Firefox for best performance
- Close unnecessary browser tabs
- Ensure stable internet connection (5+ Mbps recommended)
- Use wired connection instead of WiFi when possible

## Development

### File Structure
```
milo-meet/
├── api/                    # API endpoints
│   ├── create_meeting.php
│   ├── check_meeting.php
│   └── get_recent_meetings.php
├── assets/                 # Static assets
│   ├── css/
│   │   ├── style.css      # Main styles
│   │   └── meeting.css    # Meeting room styles
│   └── js/
│       ├── main.js        # Homepage functionality
│       ├── meeting.js     # Meeting room functionality
│       ├── webrtc.js      # WebRTC handling
│       └── chat.js        # Chat functionality
├── config/
│   └── database.php       # Database configuration
├── includes/
│   └── functions.php      # PHP utility functions
├── index.php              # Homepage
├── login.php              # Login/Register page
├── meeting.php            # Meeting room
├── logout.php             # Logout handler
└── README.md              # This file
```

### Adding Features
The codebase is modular and easy to extend:
- Add new API endpoints in `api/` directory
- Extend database schema in `config/database.php`
- Add utility functions in `includes/functions.php`
- Extend frontend functionality in respective JS files

## Security Considerations

### 🔐 **Advanced Security Features**
- **AES-256 Encryption**: All API data is encrypted using AES-256-CBC with HMAC verification
- **Request Signing**: Every API request is signed to prevent tampering
- **Rate Limiting**: Built-in rate limiting (100 requests per minute per IP)
- **CSRF Protection**: Cross-site request forgery protection on all forms
- **SQL Injection Protection**: All database queries use prepared statements
- **XSS Protection**: Input sanitization and output encoding
- **Session Security**: Secure session management with token rotation
- **Replay Attack Prevention**: Timestamp-based request validation (5-minute window)
- **Security Logging**: All security events are logged with IP tracking
- **Token Expiration**: API tokens expire after 1 hour and auto-refresh

### 🛡️ **Data Protection**
- **End-to-End Encryption**: API responses are encrypted before transmission
- **Password Hashing**: Bcrypt with salt for all passwords
- **Sensitive Data Encryption**: Meeting titles, user names, and messages are encrypted
- **Secure Headers**: Full security headers implementation (HSTS, CSP, etc.)
- **IP Tracking**: Real IP detection through proxies and CDNs
- **Input Validation**: All user inputs are validated and sanitized

### 🚨 **Security Monitoring**
- **Failed Login Attempts**: Tracked and logged
- **API Abuse Detection**: Automatic detection of suspicious activity
- **XSS Attempt Detection**: Real-time XSS attempt blocking
- **Token Validation**: Continuous token validation and rotation
- **Security Event Logging**: Comprehensive security event tracking

### 🔧 **Production Security Setup**
1. **Change Encryption Keys**: Update all keys in `config/security.php`
2. **Enable HTTPS**: SSL certificate required for all features
3. **Configure WAF**: Web Application Firewall recommended
4. **Regular Updates**: Keep PHP and dependencies updated
5. **Monitor Logs**: Regular security log monitoring
6. **Backup Encryption**: Encrypt database backups

## License

This project is licensed under the MIT License. See LICENSE file for details.

## Support

For support and questions:
- Check the troubleshooting section above
- Review browser console for error messages
- Ensure all prerequisites are met
- Verify HTTPS is properly configured

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

---

**Milo Meet** - Professional video conferencing made simple and beautiful.