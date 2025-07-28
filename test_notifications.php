<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            max-width: 300px;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background-color: #10b981;
            border-left: 4px solid #059669;
        }
        
        .notification.error {
            background-color: #ef4444;
            border-left: 4px solid #dc2626;
        }
        
        .notification.info {
            background-color: #3b82f6;
            border-left: 4px solid #2563eb;
        }
    </style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Notification System Test</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Success Notifications</h2>
                <div class="space-y-3">
                    <button onclick="showNotification('Appointment scheduled successfully!', 'success')" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Success Message
                    </button>
                    <button onclick="showNotification('User added successfully!', 'success')" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        User Added
                    </button>
                    <button onclick="showNotification('Data saved successfully!', 'success')" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Data Saved
                    </button>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Error Notifications</h2>
                <div class="space-y-3">
                    <button onclick="showNotification('Error loading data', 'error')" class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Error Message
                    </button>
                    <button onclick="showNotification('Failed to save data', 'error')" class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Save Failed
                    </button>
                    <button onclick="showNotification('Network connection error', 'error')" class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Network Error
                    </button>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Info Notifications</h2>
                <div class="space-y-3">
                    <button onclick="showNotification('Viewing patient details', 'info')" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Info Message
                    </button>
                    <button onclick="showNotification('Loading data...', 'info')" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Loading Data
                    </button>
                    <button onclick="showNotification('Processing request', 'info')" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Processing
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mt-8 bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test Dashboard Functions</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button onclick="testScheduleAppointment()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                    Test Schedule Appointment
                </button>
                <button onclick="testBookAppointment()" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                    Test Book Appointment
                </button>
                <button onclick="testRecordVitals()" class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-600">
                    Test Record Vitals
                </button>
                <button onclick="testAddUser()" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                    Test Add User
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer"></div>

    <script>
        // Notification function (same as in dashboards)
        function showNotification(message, type = 'success') {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            container.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Hide notification after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    container.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Test functions that simulate dashboard actions
        function testScheduleAppointment() {
            showNotification('Schedule appointment function called', 'info');
            setTimeout(() => {
                showNotification('Appointment scheduled successfully!', 'success');
            }, 1000);
        }

        function testBookAppointment() {
            showNotification('Book appointment function called', 'info');
            setTimeout(() => {
                showNotification('Appointment booked successfully!', 'success');
            }, 1000);
        }

        function testRecordVitals() {
            showNotification('Record vitals function called', 'info');
            setTimeout(() => {
                showNotification('Vitals recorded successfully!', 'success');
            }, 1000);
        }

        function testAddUser() {
            showNotification('Add user function called', 'info');
            setTimeout(() => {
                showNotification('User added successfully!', 'success');
            }, 1000);
        }
    </script>
</body>
</html>