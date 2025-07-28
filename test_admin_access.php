<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Admin Access Test - Can Admin See All Patients & Doctors?</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Test Patients API</h2>
                <button onclick="testPatientsAPI()" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4">
                    Test Patients API
                </button>
                <div id="patientsResult" class="bg-gray-100 p-4 rounded text-sm font-mono h-64 overflow-y-auto">
                    <!-- Results will appear here -->
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Test Doctors API</h2>
                <button onclick="testDoctorsAPI()" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mb-4">
                    Test Doctors API
                </button>
                <div id="doctorsResult" class="bg-gray-100 p-4 rounded text-sm font-mono h-64 overflow-y-auto">
                    <!-- Results will appear here -->
                </div>
            </div>
        </div>
        
        <div class="mt-8 bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Test Appointment Booking</h2>
            <button onclick="testAppointmentBooking()" class="w-full bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 mb-4">
                Test Appointment Booking
            </button>
            <div id="appointmentResult" class="bg-gray-100 p-4 rounded text-sm font-mono h-64 overflow-y-auto">
                <!-- Results will appear here -->
            </div>
        </div>
    </div>

    <script>
        function log(elementId, message) {
            const element = document.getElementById(elementId);
            const timestamp = new Date().toLocaleTimeString();
            element.innerHTML += `<div>[${timestamp}] ${message}</div>`;
            element.scrollTop = element.scrollHeight;
            console.log(message);
        }

        async function testPatientsAPI() {
            const resultDiv = document.getElementById('patientsResult');
            resultDiv.innerHTML = '<div>Testing Patients API...</div>';
            
            try {
                log('patientsResult', '🔵 Testing Patients API...');
                const response = await fetch('api/patients.php?action=list');
                log('patientsResult', `📡 Response status: ${response.status}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                log('patientsResult', `📊 API Response: ${JSON.stringify(result, null, 2)}`);
                
                if (result.success) {
                    log('patientsResult', `✅ Success! Found ${result.data.length} patients`);
                    result.data.forEach((patient, index) => {
                        log('patientsResult', `${index + 1}. ${patient.first_name} ${patient.last_name} (ID: ${patient.id})`);
                    });
                } else {
                    log('patientsResult', `❌ Error: ${result.message}`);
                }
            } catch (error) {
                log('patientsResult', `❌ Error: ${error.message}`);
            }
        }

        async function testDoctorsAPI() {
            const resultDiv = document.getElementById('doctorsResult');
            resultDiv.innerHTML = '<div>Testing Doctors API...</div>';
            
            try {
                log('doctorsResult', '🟢 Testing Doctors API...');
                const response = await fetch('api/doctors.php?action=list');
                log('doctorsResult', `📡 Response status: ${response.status}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                log('doctorsResult', `📊 API Response: ${JSON.stringify(result, null, 2)}`);
                
                if (result.success) {
                    log('doctorsResult', `✅ Success! Found ${result.data.length} doctors`);
                    result.data.forEach((doctor, index) => {
                        log('doctorsResult', `${index + 1}. Dr. ${doctor.first_name} ${doctor.last_name} (${doctor.specialization})`);
                    });
                } else {
                    log('doctorsResult', `❌ Error: ${result.message}`);
                }
            } catch (error) {
                log('doctorsResult', `❌ Error: ${error.message}`);
            }
        }

        async function testAppointmentBooking() {
            const resultDiv = document.getElementById('appointmentResult');
            resultDiv.innerHTML = '<div>Testing Appointment Booking...</div>';
            
            try {
                log('appointmentResult', '🟣 Testing Appointment Booking...');
                
                // Test appointment creation
                const appointmentData = {
                    patient_id: 1,
                    doctor_id: 1,
                    appointment_date: '2024-01-15',
                    appointment_time: '10:00:00',
                    appointment_type: 'consultation',
                    reason: 'Test appointment',
                    notes: 'Test appointment booking'
                };
                
                log('appointmentResult', `📝 Attempting to book appointment: ${JSON.stringify(appointmentData, null, 2)}`);
                
                const response = await fetch('api/appointments.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(appointmentData)
                });
                
                log('appointmentResult', `📡 Response status: ${response.status}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                log('appointmentResult', `📊 API Response: ${JSON.stringify(result, null, 2)}`);
                
                if (result.success) {
                    log('appointmentResult', `✅ Success! Appointment booked successfully`);
                } else {
                    log('appointmentResult', `❌ Error: ${result.message}`);
                }
            } catch (error) {
                log('appointmentResult', `❌ Error: ${error.message}`);
            }
        }
    </script>
</body>
</html>