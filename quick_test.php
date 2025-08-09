<?php
/**
 * ملف اختبار سريع لـ JWT Authentication
 * استخدم هذا الملف لاختبار API بعد إعداد JWT
 */

echo "=== اختبار JWT Authentication ===\n\n";

// 1. اختبار Register
echo "1. اختبار Register...\n";
$registerData = [
    'name' => 'Test User',
    'email' => 'test' . time() . '@example.com', // email فريد
    'password' => 'password123'
];

$registerResult = makeRequest('http://127.0.0.1:8000/api/register', 'POST', $registerData);
echo "Register Response: " . $registerResult . "\n\n";

// 2. اختبار Login
echo "2. اختبار Login...\n";
$loginData = [
    'email' => $registerData['email'],
    'password' => $registerData['password']
];

$loginResult = makeRequest('http://127.0.0.1:8000/api/login', 'POST', $loginData);
echo "Login Response: " . $loginResult . "\n\n";

// استخراج الـ token من response
$loginResponse = json_decode($loginResult, true);
$token = $loginResponse['token'] ?? null;

if ($token) {
    echo "✅ تم الحصول على الـ token بنجاح!\n\n";
    
    // 3. اختبار Get User Profile
    echo "3. اختبار Get User Profile...\n";
    $profileResult = makeRequest('http://127.0.0.1:8000/api/user-profile', 'GET', [], $token);
    echo "Profile Response: " . $profileResult . "\n\n";
    
    // 4. اختبار Refresh Token
    echo "4. اختبار Refresh Token...\n";
    $refreshResult = makeRequest('http://127.0.0.1:8000/api/refresh', 'POST', [], $token);
    echo "Refresh Response: " . $refreshResult . "\n\n";
    
    // 5. اختبار Logout
    echo "5. اختبار Logout...\n";
    $logoutResult = makeRequest('http://127.0.0.1:8000/api/logout', 'POST', [], $token);
    echo "Logout Response: " . $logoutResult . "\n\n";
    
} else {
    echo "❌ فشل في الحصول على الـ token\n";
}

echo "=== انتهى الاختبار ===\n";

/**
 * دالة لإنشاء HTTP requests
 */
function makeRequest($url, $method, $data = [], $token = null) {
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => $method,
        ]
    ];
    
    // إضافة Authorization header إذا كان هناك token
    if ($token) {
        $options['http']['header'] .= "Authorization: Bearer $token\r\n";
    }
    
    // إضافة البيانات للـ POST requests
    if ($method === 'POST' && !empty($data)) {
        $options['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === false) {
        return "Error: Failed to connect to $url";
    }
    
    return $result;
} 