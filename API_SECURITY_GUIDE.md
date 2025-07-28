# Hospital CRM API Security Guide

## 🔒 Security Implementation Overview

Humne Hospital CRM system mein comprehensive API security implement ki hai jo ensure karti hai ki sirf authorized users hi APIs access kar sakte hain aur woh bhi sirf apne permitted data ke saath.

## 🛡️ Security Features

### 1. **Authentication Required**
- Sabhi API endpoints require user login
- Bina login kiye koi bhi API accessible nahi hai
- Session-based authentication with database tokens

### 2. **Role-Based Access Control (RBAC)**
- **Admin**: Full access to all data and operations
- **Doctor**: Limited access to assigned patients and own data
- **Patient**: Access only to own data

### 3. **Data Filtering by Role**
```php
// Example: Patients API
if ($this->isAdmin()) {
    // Admin sees all patients
} elseif ($this->isDoctor()) {
    // Doctor sees only assigned patients
} elseif ($this->isPatient()) {
    // Patient sees only own data
}
```

## 📋 API Access Matrix

| Endpoint | Admin | Doctor | Patient | Notes |
|----------|-------|--------|---------|--------|
| **Patients API** |
| GET /list | ✅ All patients | ✅ Assigned patients | ✅ Own data only | Role-filtered results |
| GET /get/:id | ✅ Any patient | ✅ If assigned | ✅ Own data only | Access validation |
| POST /create | ✅ Create any | ❌ Not allowed | ❌ Not allowed | Admin only |
| PUT /update/:id | ✅ Full update | ✅ Assigned patients | ✅ Limited fields | Role-based field restrictions |
| DELETE /:id | ✅ Any patient | ❌ Not allowed | ❌ Not allowed | Admin only |
| **Doctors API** |
| GET /list | ✅ Full details | ✅ Basic info | ✅ Public info | Different detail levels |
| GET /get/:id | ✅ Full details | ✅ Own full, others basic | ✅ Public info | Access level varies |
| POST /create | ✅ Create any | ❌ Not allowed | ❌ Not allowed | Admin only |
| PUT /update/:id | ✅ Any doctor | ✅ Own data only | ❌ Not allowed | Self-update allowed |
| DELETE /:id | ✅ Any doctor | ❌ Not allowed | ❌ Not allowed | Admin only |
| **Appointments API** |
| GET /list | ✅ All appointments | ✅ Own appointments | ✅ Own appointments | Role-filtered |
| GET /get/:id | ✅ Any appointment | ✅ If involved | ✅ If involved | Access validation |
| POST /create | ✅ For anyone | ✅ For assigned patients | ✅ For self only | Permission checks |
| PUT /update/:id | ✅ Full update | ✅ Notes & status | ✅ Reason only | Field restrictions |
| DELETE /:id | ✅ Cancel any | ✅ Cancel own | ✅ Cancel own | Soft delete (status change) |

## 🔧 Implementation Details

### ApiBase Class
```php
class ApiBase {
    // Automatic authentication check
    private function checkAuthentication()
    
    // Role-based access control
    private function checkRoleAccess()
    
    // Permission helpers
    protected function canAccessPatient($patient_id)
    protected function canAccessDoctor($doctor_id)
    
    // Security logging
    protected function logAccess($action, $resource_id = null)
}
```

### Access Control Examples

#### 1. Patient Data Access
```php
protected function canAccessPatient($patient_id) {
    if ($this->isAdmin()) {
        return true; // Admin can access all
    }
    
    if ($this->isDoctor()) {
        // Doctor can access assigned patients only
        $query = "SELECT COUNT(*) FROM patients p 
                 LEFT JOIN doctors d ON p.assigned_doctor_id = d.doctor_id 
                 WHERE p.patient_id = :patient_id AND d.user_id = :user_id";
        // ... check query
    }
    
    if ($this->isPatient()) {
        // Patient can access own data only
        $query = "SELECT COUNT(*) FROM patients 
                 WHERE patient_id = :patient_id AND user_id = :user_id";
        // ... check query
    }
    
    return false;
}
```

#### 2. Field-Level Restrictions
```php
// Patients can only update limited fields
if ($this->isPatient()) {
    $allowed_fields = ['phone', 'address', 'emergency_contact_name', 'emergency_contact_phone'];
} else {
    $allowed_fields = ['first_name', 'last_name', 'email', 'phone', 'address', 'blood_group', ...];
}
```

## 🚫 What's Protected

### 1. **Direct URL Access Blocked**
- APIs cannot be accessed directly without authentication
- Example: `http://localhost/api/patients.php?action=list` → **401 Unauthorized**

### 2. **Cross-Role Data Access Prevented**
- Doctor cannot see other doctors' patients
- Patient cannot see other patients' data
- Only admin has full access

### 3. **Operation Restrictions**
- Patients cannot create/delete other users
- Doctors cannot delete patients
- Role-appropriate CRUD operations only

### 4. **Data Leakage Prevention**
- Sensitive fields filtered based on role
- Different detail levels for different roles
- No unauthorized data exposure

## 📝 Security Logging

Har API call log hoti hai with:
```php
$log_data = [
    'user_id' => $this->getCurrentUserId(),
    'role' => $this->getCurrentUserRole(),
    'action' => $action,
    'resource_id' => $resource_id,
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'timestamp' => date('Y-m-d H:i:s')
];
```

## 🔄 Migration from Old APIs

### Before (Insecure)
```php
// Old code - anyone could access
$allow_testing = true;
if (!$auth->isLoggedIn() && !$allow_testing) {
    // Commented out security checks
}
```

### After (Secure)
```php
// New code - mandatory authentication
class PatientsApi extends ApiBase {
    public function __construct() {
        parent::__construct(['admin', 'doctor', 'patient']);
    }
}
```

## 🎯 Usage Examples

### 1. Admin Access
```bash
# Admin can access all patients
GET /api/patients.php?action=list
# Returns: All patients with full details
```

### 2. Doctor Access
```bash
# Doctor can access assigned patients only
GET /api/patients.php?action=list
# Returns: Only assigned patients
```

### 3. Patient Access
```bash
# Patient can access own data only
GET /api/patients.php?action=list
# Returns: Own patient record only
```

## ⚠️ Important Notes

1. **No Testing Backdoors**: All `$allow_testing` flags removed
2. **Mandatory Authentication**: Every API call requires valid session
3. **Role Validation**: Each endpoint validates user role
4. **Data Filtering**: Results filtered based on user permissions
5. **Security Logging**: All access attempts logged for monitoring

## 🔍 Testing Security

### Test Cases:
1. **Unauthenticated Access**: Should return 401
2. **Cross-Role Access**: Should return 403
3. **Invalid Resource Access**: Should return 403/404
4. **Field Restrictions**: Should only allow permitted fields
5. **Data Filtering**: Should only return permitted data

### Example Test:
```bash
# Without login - should fail
curl http://localhost/api/patients.php?action=list
# Response: {"success": false, "message": "Unauthorized access. Please login first.", "code": 401}

# Patient trying to access all patients - should return only own data
curl -H "Cookie: session_id=patient_session" http://localhost/api/patients.php?action=list
# Response: Only patient's own data
```

## 🎉 Benefits

1. **Complete Security**: No unauthorized access possible
2. **Role-Based Control**: Each role gets appropriate access
3. **Data Protection**: Sensitive data protected from unauthorized access
4. **Audit Trail**: All API access logged for security monitoring
5. **Scalable**: Easy to add new roles and permissions

Yeh implementation ensure karti hai ki aapka Hospital CRM system completely secure hai aur koi bhi unauthorized access nahi ho sakti! 🔒✅