# Hospital CRM System

A comprehensive Hospital Customer Relationship Management (CRM) system built with PHP and MySQL featuring role-based access control, appointment management, patient vitals tracking, and modern responsive design with dark/light theme support.

## Features

### 🏥 Multi-Role System
- **Admin Dashboard**: Complete system management
- **Doctor Portal**: Patient management and appointments
- **Patient Portal**: View appointments and vitals

### 🔐 Security Features
- Password complexity validation (8+ chars, uppercase, lowercase, numbers, special characters)
- Secure password hashing (bcrypt)
- Session-based authentication
- Role-based access control
- Protection against unauthorized access

### 📊 Core Functionality
- **User Management**: CRUD operations for doctors, patients, and admins
- **Appointment System**: Schedule, view, and manage appointments
- **Vital Signs Tracking**: Record and monitor patient vitals with trend analysis
- **Prescription Management**: Digital prescription handling
- **Doctor-Patient Assignment**: Controlled access to patient data

### 🎨 Modern UI/UX
- Responsive design (mobile-first approach)
- Dark/Light theme toggle
- Modern gradient backgrounds
- Smooth animations and transitions
- Intuitive navigation
- Clean typography and spacing

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3 (Tailwind CSS), JavaScript (ES6+)
- **Icons**: Font Awesome 6
- **Charts**: Chart.js
- **Authentication**: Session-based with database tokens

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher (MariaDB 10.4+ also supported)
- Web server (Apache/Nginx)
- Composer (optional)

### Database Setup
1. Create a MySQL database named `hospital_crm`
2. Import the database structure using either:
   - `database/schema.sql` (basic structure)
   - `database/hospital_crm_updated.sql` (complete structure with sample data)

### Default Login Credentials
After setting up the database with sample data, you can use these credentials:

**Admin Account:**
- Username: `admin`
- Email: `admin11@hospital.com`
- Password: `password123` (default password for all sample accounts)

**Doctor Accounts:**
- Username: `john.smith` | Email: `john.smith@hospital.com` | Specialization: Cardiology
- Username: `sarah.johnson` | Email: `sarah.johnson@hospital.com` | Specialization: Neurology  
- Username: `michael.brown` | Email: `michael.brown@hospital.com` | Specialization: Pediatrics

**Patient Accounts:**
- Username: `alice.wilson` | Email: `alice.wilson@email.com`
- Username: `bob.davis` | Email: `bob.davis@email.com`
- Username: `carol.miller` | Email: `carol.miller@email.com`
- Username: `david.garcia` | Email: `david.garcia@email.com`
- Username: `emma.taylor` | Email: `emma.taylor@email.com`

### Step 1: Clone Repository
```bash
git clone <repository-url>
cd hospital-crm
```

### Step 2: Database Setup
1. Create a MySQL database named `hospital_crm`
2. Import the database schema:
```bash
mysql -u username -p hospital_crm < database/schema.sql
```

### Step 3: Configuration
1. Update database credentials in `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'hospital_crm';
private $username = 'your_username';
private $password = 'your_password';
```

### Step 4: Web Server Setup
- Place files in your web server's document root
- Ensure proper permissions for PHP to read/write files
- Configure virtual host (optional)

### Step 5: Access the System
- Open your browser and navigate to the application URL
- Use default admin credentials:
  - **Email**: admin@hospital.com
  - **Password**: password123

## Default Users

The system comes with a default admin user. You can create additional users through the admin dashboard.

### Admin Credentials
- **Email**: admin@hospital.com
- **Password**: password123

## Database Structure

### Core Tables
- `users` - Base user information
- `doctors` - Doctor-specific details
- `patients` - Patient-specific details
- `appointments` - Appointment scheduling
- `prescriptions` - Medical prescriptions
- `patient_vitals` - Vital signs tracking
- `vital_types` - Configurable vital types
- `doctor_patient_assignments` - Access control
- `user_sessions` - Session management

## API Endpoints

### Users API (`/api/users.php`)
- `POST` - Create new user
- `GET` - Retrieve users
- `PUT` - Update user
- `DELETE` - Deactivate user

### Appointments API (`/api/appointments.php`)
- `POST` - Create appointment
- `GET` - Retrieve appointments
- `PUT` - Update appointment
- `DELETE` - Cancel appointment

### Vitals API (`/api/vitals.php`)
- `POST` - Record vitals
- `GET` - Retrieve vital data
- `PUT` - Update vital types

## Security Considerations

### Password Policy
- Minimum 8 characters
- Must contain uppercase letters
- Must contain lowercase letters
- Must contain numbers
- Must contain special characters

### Access Control
- Role-based permissions
- Session timeout
- Secure password hashing
- SQL injection prevention (PDO prepared statements)
- XSS protection (input sanitization)

### Data Privacy
- Doctors can only access assigned patients
- Patients can only view their own data
- Contact details are protected between roles
- Session tracking for audit trails

## File Structure

```
hospital-crm/
├── classes/
│   ├── Auth.php          # Authentication handling
│   ├── User.php          # User management
│   ├── Appointment.php   # Appointment management
│   └── Vitals.php        # Vitals tracking
├── config/
│   └── database.php      # Database configuration
├── dashboard/
│   ├── admin.php         # Admin dashboard
│   ├── doctor.php        # Doctor dashboard
│   └── patient.php       # Patient dashboard
├── database/
│   └── schema.sql        # Database schema
├── api/
│   ├── users.php         # User API endpoints
│   ├── appointments.php  # Appointment API
│   └── vitals.php        # Vitals API
├── index.php             # Login page
├── logout.php            # Logout handler
└── README.md             # Documentation
```

## Features Roadmap

### Completed ✅
- User authentication and authorization
- Role-based dashboards
- User management (CRUD)
- Appointment system foundation
- Vitals tracking system
- Modern responsive UI
- Dark/Light theme

### In Progress 🚧
- Complete appointment management
- Prescription system
- Patient profile management
- Vital signs visualization
- Email notifications

### Planned 📋
- Report generation
- Mobile app API
- SMS notifications
- Advanced analytics
- File upload for medical records
- Integration with external systems

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Support

For issues and questions:
- Check the documentation
- Review existing issues
- Create a new issue with detailed description

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Tailwind CSS for styling framework
- Font Awesome for icons
- Chart.js for data visualization
- PHP community for excellent documentation

---

**Note**: This is a demonstration system. For production use, implement additional security measures, backup systems, and proper server configuration.