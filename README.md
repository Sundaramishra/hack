# Hospital CRM System

A comprehensive Hospital Customer Relationship Management system built with PHP, MySQL, and modern web technologies.

## 🏥 Features

### Multi-Role System
- **Admin Dashboard**: Complete system management with CRUD operations
- **Doctor Portal**: Patient management, appointment scheduling, and medical records
- **Patient Portal**: Appointment booking, medical history, and vitals tracking

### Core Functionality
- **Secure Authentication**: Strong password requirements (8+ chars, uppercase, lowercase, numbers, special characters)
- **Role-Based Access Control**: Strict data access based on user roles
- **Appointment Management**: Smart scheduling with time slot availability
- **Vitals Tracking**: Comprehensive patient health monitoring
- **Custom Vitals**: Admin-configurable additional vital signs
- **Responsive Design**: Mobile-first approach with dark/light themes

### Security Features
- **Session Management**: Secure user sessions with role validation
- **Data Privacy**: Doctors can only see assigned patients, patients see limited doctor info
- **Input Validation**: Server-side validation for all user inputs
- **Password Hashing**: BCrypt encryption for all passwords

## 🚀 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.4+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Tailwind CSS
- **Icons**: Font Awesome 6
- **Database Access**: PDO with prepared statements

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Web server (Apache/Nginx) or XAMPP for local development
- Modern web browser

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/hospital-crm.git
cd hospital-crm
```

### 2. Database Setup
1. Create a MySQL database named `hospital_crm`
2. Import the database schema:
```bash
mysql -u root -p hospital_crm < database/hospital_crm.sql
```

### 3. Configuration
1. Update database credentials in `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'hospital_crm';
private $username = 'your_username';
private $password = 'your_password';
```

### 4. Web Server Setup
- **XAMPP**: Place files in `htdocs/hospital-crm/`
- **Apache**: Configure virtual host pointing to project directory
- **Nginx**: Configure server block for the project

### 5. Access the Application
Visit `http://localhost/hospital-crm` in your web browser

## 👥 Default Accounts

| Role | Username | Email | Password |
|------|----------|-------|----------|
| Admin | admin | admin@hospital.com | Hospital@123 |
| Doctor | dr_sharma | dr.sharma@hospital.com | Hospital@123 |
| Doctor | dr_patel | dr.patel@hospital.com | Hospital@123 |
| Patient | patient_john | john@email.com | Hospital@123 |
| Patient | patient_jane | jane@email.com | Hospital@123 |

## 📊 Database Schema

### Core Tables
- **users**: Base user information and authentication
- **doctors**: Doctor-specific data (specialization, availability, fees)
- **patients**: Patient-specific data (medical history, assigned doctor)
- **appointments**: Appointment scheduling and management
- **vitals**: Patient vital signs and measurements
- **custom_vitals**: Admin-defined additional vital types
- **patient_custom_vitals**: Values for custom vital signs

### Key Relationships
- Users → Doctors/Patients (1:1)
- Doctors → Patients (1:Many for assigned patients)
- Patients → Appointments (1:Many)
- Doctors → Appointments (1:Many)
- Patients → Vitals (1:Many)

## 🔐 Security Considerations

### Password Requirements
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character

### Access Control
- **Admins**: Full system access and CRUD operations
- **Doctors**: Can only access assigned patients and their own appointments
- **Patients**: Can view all doctors but only their own medical data

### Data Protection
- All database queries use prepared statements
- Session-based authentication with proper validation
- No sensitive information exposed in frontend code

## 🎨 User Interface

### Design Features
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Dark/Light Themes**: User preference-based theme switching
- **Modern UI**: Clean, professional interface using Tailwind CSS
- **Accessibility**: Proper contrast ratios and keyboard navigation

### Dashboard Features
- **Real-time Statistics**: Live data updates for key metrics
- **Interactive Tables**: Sortable and searchable data tables
- **Modal Forms**: User-friendly forms for data entry
- **Time Slot Visualization**: Visual appointment scheduling

## 📱 Mobile Responsiveness

The system is fully responsive with:
- Collapsible sidebar navigation
- Touch-friendly buttons and forms
- Optimized layouts for small screens
- Swipe gestures for mobile navigation

## 🔧 Customization

### Adding Custom Vitals
Admins can add new vital sign types through the Custom Vitals section:
1. Navigate to Admin Dashboard → Custom Vitals
2. Click "Add Custom Vital"
3. Define name, unit, and normal ranges
4. Save and assign to patients

### Theme Customization
Modify `tailwind.config` in the HTML files to customize:
- Color schemes
- Font families
- Spacing and sizing
- Animation durations

## 🐛 Troubleshooting

### Common Issues

**Database Connection Errors**
- Verify MySQL service is running
- Check database credentials in `config/database.php`
- Ensure database exists and is accessible

**Permission Errors**
- Check file permissions (755 for directories, 644 for files)
- Ensure web server has read access to all files

**Session Issues**
- Verify PHP session configuration
- Check if session directory is writable
- Clear browser cookies if needed

## 📈 Future Enhancements

- **Email Notifications**: Appointment reminders and confirmations
- **SMS Integration**: Text message notifications
- **Report Generation**: PDF reports for medical records
- **API Integration**: Third-party medical system integration
- **Multi-language Support**: Internationalization
- **Advanced Analytics**: Detailed reporting and insights

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- Create an issue on GitHub
- Email: support@hospitalcrm.com
- Documentation: [Wiki](https://github.com/yourusername/hospital-crm/wiki)

## 🙏 Acknowledgments

- Tailwind CSS for the amazing utility-first CSS framework
- Font Awesome for the comprehensive icon library
- PHP community for excellent documentation and resources