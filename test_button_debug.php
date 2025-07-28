<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Button Debug Test</title>
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
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Button Debug Test</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Doctor Dashboard Buttons</h2>
                <div class="space-y-3">
                    <button onclick="testScheduleAppointment()" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Test Schedule Appointment
                    </button>
                    <button onclick="testRecordVitals()" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Test Record Vitals
                    </button>
                    <button onclick="testViewPatient()" class="w-full bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                        Test View Patient
                    </button>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Patient Dashboard Buttons</h2>
                <div class="space-y-3">
                    <button onclick="testBookAppointment()" class="w-full bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                        Test Book Appointment
                    </button>
                    <button onclick="testViewAppointment()" class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Test View Appointment
                    </button>
                    <button onclick="testRequestReschedule()" class="w-full bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                        Test Request Reschedule
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mt-8 bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Console Log</h2>
            <div id="consoleLog" class="bg-gray-100 p-4 rounded text-sm font-mono h-64 overflow-y-auto">
                <!-- Console messages will appear here -->
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer"></div>

    <script>
        // Notification function
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

        // Console logging function
        function log(message) {
            const consoleLog = document.getElementById('consoleLog');
            const timestamp = new Date().toLocaleTimeString();
            consoleLog.innerHTML += `<div>[${timestamp}] ${message}</div>`;
            consoleLog.scrollTop = consoleLog.scrollHeight;
            console.log(message);
        }

        // Test functions that simulate dashboard actions
        function testScheduleAppointment() {
            log('🔵 Schedule Appointment button clicked');
            showNotification('Schedule appointment function called', 'info');
            
            // Simulate the actual function
            setTimeout(() => {
                log('📋 Opening appointment modal...');
                showNotification('Appointment modal opened', 'info');
                
                setTimeout(() => {
                    log('✅ Appointment scheduled successfully!');
                    showNotification('Appointment scheduled successfully!', 'success');
                }, 1000);
            }, 500);
        }

        function testRecordVitals() {
            log('🟢 Record Vitals button clicked');
            showNotification('Record vitals function called', 'info');
            
            setTimeout(() => {
                log('📊 Recording vitals...');
                showNotification('Recording vitals...', 'info');
                
                setTimeout(() => {
                    log('✅ Vitals recorded successfully!');
                    showNotification('Vitals recorded successfully!', 'success');
                }, 1000);
            }, 500);
        }

        function testViewPatient() {
            log('🟣 View Patient button clicked');
            showNotification('View patient function called', 'info');
        }

        function testBookAppointment() {
            log('🟠 Book Appointment button clicked');
            showNotification('Book appointment function called', 'info');
            
            setTimeout(() => {
                log('📅 Opening book appointment modal...');
                showNotification('Book appointment modal opened', 'info');
                
                setTimeout(() => {
                    log('✅ Appointment booked successfully!');
                    showNotification('Appointment booked successfully!', 'success');
                }, 1000);
            }, 500);
        }

        function testViewAppointment() {
            log('🔴 View Appointment button clicked');
            showNotification('View appointment function called', 'info');
        }

        function testRequestReschedule() {
            log('🟡 Request Reschedule button clicked');
            showNotification('Request reschedule function called', 'info');
        }

        // Initialize
        log('🚀 Button Debug Test initialized');
        log('Click any button to test functionality...');
    </script>
</body>
</html>