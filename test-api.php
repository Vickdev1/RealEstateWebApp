<?php
echo "Testing API Endpoints...\n\n";

$base_url = "http://localhost:8000/api";

// Test properties endpoint
echo "1. Testing GET /api/properties.php\n";
$response = file_get_contents("$base_url/properties.php");
$data = json_decode($response, true);
echo "   Status: " . ($data['success'] ? '✅ Success' : '❌ Failed') . "\n";
echo "   Properties found: " . count($data['data'] ?? []) . "\n\n";

// Test services endpoint
echo "2. Testing GET /api/services.php\n";
$response = file_get_contents("$base_url/services.php");
$data = json_decode($response, true);
echo "   Status: " . ($data['success'] ? '✅ Success' : '❌ Failed') . "\n";
echo "   Services found: " . count($data['data'] ?? []) . "\n\n";

// Test testimonials endpoint
echo "3. Testing GET /api/testimonials.php\n";
$response = file_get_contents("$base_url/testimonials.php");
$data = json_decode($response, true);
echo "   Status: " . ($data['success'] ? '✅ Success' : '❌ Failed') . "\n";
echo "   Testimonials found: " . count($data['data'] ?? []) . "\n\n";

// Test contact endpoint (POST)
echo "4. Testing POST /api/contact.php\n";
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0712345678',
            'message' => 'This is a test message'
        ])
    ]
]);
$response = file_get_contents("$base_url/contact.php", false, $context);
$data = json_decode($response, true);
echo "   Status: " . ($data['success'] ? '✅ Success' : '❌ Failed') . "\n";
echo "   Message: " . ($data['message'] ?? 'N/A') . "\n\n";

echo "All tests completed!\n";