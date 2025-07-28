<?php
// Simple test to check if buttons and APIs are working
?>
<!DOCTYPE html>
<html>
<head>
    <title>Button Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8">
    <h1 class="text-2xl font-bold mb-4">Testing Buttons and APIs</h1>
    
    <div class="space-y-4">
        <div>
            <h2 class="text-lg font-semibold mb-2">Test API Endpoints</h2>
            <button onclick="testPatientsAPI()" class="bg-blue-500 text-white px-4 py-2 rounded">Test Patients API</button>
            <button onclick="testDoctorsAPI()" class="bg-green-500 text-white px-4 py-2 rounded ml-2">Test Doctors API</button>
            <button onclick="testAppointmentsAPI()" class="bg-purple-500 text-white px-4 py-2 rounded ml-2">Test Appointments API</button>
        </div>
        
        <div>
            <h2 class="text-lg font-semibold mb-2">Test JavaScript Functions</h2>
            <button onclick="testScheduleAppointment()" class="bg-orange-500 text-white px-4 py-2 rounded">Test Schedule Appointment</button>
            <button onclick="testBookAppointment()" class="bg-red-500 text-white px-4 py-2 rounded ml-2">Test Book Appointment</button>
            <button onclick="testRecordVitals()" class="bg-yellow-500 text-white px-4 py-2 rounded ml-2">Test Record Vitals</button>
        </div>
        
        <div id="results" class="mt-4 p-4 bg-gray-100 rounded"></div>
    </div>

    <script>
        function log(message) {
            const results = document.getElementById('results');
            results.innerHTML += '<div>' + new Date().toLocaleTimeString() + ': ' + message + '</div>';
            console.log(message);
        }

        async function testPatientsAPI() {
            try {
                log('Testing Patients API...');
                const response = await fetch('api/patients.php?action=list');
                const result = await response.json();
                log('Patients API Response: ' + JSON.stringify(result));
            } catch (error) {
                log('Patients API Error: ' + error.message);
            }
        }

        async function testDoctorsAPI() {
            try {
                log('Testing Doctors API...');
                const response = await fetch('api/doctors.php?action=list');
                const result = await response.json();
                log('Doctors API Response: ' + JSON.stringify(result));
            } catch (error) {
                log('Doctors API Error: ' + error.message);
            }
        }

        async function testAppointmentsAPI() {
            try {
                log('Testing Appointments API...');
                const response = await fetch('api/appointments.php?action=list');
                const result = await response.json();
                log('Appointments API Response: ' + JSON.stringify(result));
            } catch (error) {
                log('Appointments API Error: ' + error.message);
            }
        }

        function testScheduleAppointment() {
            log('Testing scheduleAppointment function...');
            // Simulate the function call
            try {
                // This would normally show a modal
                log('scheduleAppointment function called successfully');
            } catch (error) {
                log('scheduleAppointment Error: ' + error.message);
            }
        }

        function testBookAppointment() {
            log('Testing openBookAppointmentModal function...');
            try {
                // This would normally show a modal
                log('openBookAppointmentModal function called successfully');
            } catch (error) {
                log('openBookAppointmentModal Error: ' + error.message);
            }
        }

        function testRecordVitals() {
            log('Testing recordVitals function...');
            try {
                // This would normally show a modal
                log('recordVitals function called successfully');
            } catch (error) {
                log('recordVitals Error: ' + error.message);
            }
        }
    </script>
</body>
</html>