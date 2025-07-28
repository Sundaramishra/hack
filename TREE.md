# 🏥 Hospital CRM - Project Tree Structure

```
hospital-crm/
├── 📁 assets/
│   └── 📁 js/
│       └── 📄 notifications.js          # Notification system (success/error/warning)
│
├── 📁 config/
│   └── 📄 database.php                  # Database connection configuration
│
├── 📁 dashboard/
│   ├── 📄 admin.php                     # Admin dashboard with full functionality
│   ├── 📄 doctor.php                    # Doctor dashboard 
│   └── 📄 patient.php                   # Patient dashboard
│
├── 📁 database/
│   ├── 📄 hospital_crm.sql              # Complete database schema with sample data
│   └── 📄 add_vitals_tables.sql         # Additional vitals tables
│
├── 📁 handlers/
│   ├── 📄 admin_appointments.php        # Admin appointment management
│   ├── 📄 admin_stats.php              # Admin dashboard statistics
│   ├── 📄 admin_users.php              # User management (CRUD operations)
│   ├── 📄 appointments.php             # Complete appointment system
│   ├── 📄 book_appointment.php         # Appointment booking logic
│   ├── 📄 doctor_stats.php             # Doctor dashboard statistics
│   ├── 📄 get_doctors.php              # Get available doctors
│   ├── 📄 get_time_slots.php           # Get available time slots
│   ├── 📄 patient_appointments.php     # Patient appointment data
│   ├── 📄 patient_stats.php            # Patient dashboard statistics
│   ├── 📄 prescriptions.php            # Prescription management system
│   ├── 📄 profile.php                  # User profile management
│   └── 📄 vitals.php                   # Patient vitals management
│
├── 📁 includes/
│   └── 📄 auth.php                      # Authentication & authorization system
│
├── 📄 index.php                         # Main entry point (redirects to login/dashboard)
├── 📄 login.php                         # User login page with theme toggle
├── 📄 logout.php                        # Logout handler
├── 📄 README.md                         # Project documentation
├── 📄 TREE.md                          # This file - project structure
│
└── 📁 debug/ (temporary files)
    ├── 📄 debug.php                     # System debugging tool
    ├── 📄 simple_login.php             # Simple login for testing
    └── 📄 test_login.php               # Login testing tool
```

## 🎯 **Key Features by Role:**

### 👨‍💼 **Admin Dashboard:**
- ✅ **Complete Profile Management** (update info, change password, theme)
- ✅ **User Management** (Create/Read/Update/Delete users)
- ✅ **Appointment Booking** (Book for any patient with any doctor)
- ✅ **Time Slot Management** (Visual slot selection, availability check)
- ✅ **Vitals Management** (Add custom vital types, manage patient vitals)
- ✅ **Prescription Oversight** (View all prescriptions)
- ✅ **System Statistics** (Total users, appointments, etc.)

### 👨‍⚕️ **Doctor Dashboard:**
- ✅ **Profile Management** (Professional info, availability settings)
- ✅ **Patient Management** (View assigned patients only)
- ✅ **Appointment Management** (Today's schedule, patient history)
- ✅ **Prescription System** (Create prescriptions with medicines)
- ✅ **Vitals Recording** (Add/view patient vitals)
- ✅ **Schedule Management** (Availability, consultation duration)

### 🧑‍🤝‍🧑 **Patient Dashboard:**
- ✅ **Profile Management** (Personal info, medical history)
- ✅ **Appointment Booking** (Book with any doctor, view availability)
- ✅ **Prescription Viewing** (View own prescriptions and medicines)
- ✅ **Vitals History** (View own vital records)
- ✅ **Doctor Information** (View assigned doctor, specializations)

## 🔧 **Technical Stack:**

### **Backend:**
- **PHP 7.4+** - Server-side logic
- **MySQL 5.7+** - Database management
- **PDO** - Database abstraction layer
- **Sessions** - Authentication management

### **Frontend:**
- **HTML5** - Structure
- **Tailwind CSS** - Styling framework
- **JavaScript ES6+** - Interactive functionality
- **Font Awesome 6** - Icons
- **Fetch API** - AJAX requests

### **Security:**
- **bcrypt** - Password hashing
- **Role-based access control** (RBAC)
- **Session management**
- **SQL injection prevention** (PDO prepared statements)
- **XSS protection** (htmlspecialchars)

## 📊 **Database Tables:**

1. **`users`** - Base user information
2. **`doctors`** - Doctor-specific data
3. **`patients`** - Patient-specific data
4. **`appointments`** - Appointment records
5. **`prescriptions`** - Prescription data
6. **`prescription_medicines`** - Medicine details
7. **`vital_types`** - Vital sign types (default + custom)
8. **`patient_vitals`** - Patient vital records

## 🎨 **UI/UX Features:**

- ✅ **Responsive Design** - Works on all devices
- ✅ **Dark/Light Theme** - User preference
- ✅ **Clean Notifications** - No annoying popups
- ✅ **Modern Interface** - Gradient backgrounds, smooth transitions
- ✅ **Interactive Elements** - Time slot selection, modal forms
- ✅ **Accessibility** - Proper labels, keyboard navigation

## 🚀 **Installation:**

1. **Setup Database:**
   ```bash
   mysql -u root -p < database/hospital_crm.sql
   mysql -u root -p hospital_crm < database/add_vitals_tables.sql
   ```

2. **Configure Database:**
   - Update `config/database.php` with your credentials

3. **Default Accounts:**
   - **Admin:** `admin` / `Hospital@123`
   - **Doctor:** `dr_sharma` / `Hospital@123`
   - **Patient:** `patient_john` / `Hospital@123`

**Bhai ab profile bhi bilkul professional lag raha hai! 🔥**