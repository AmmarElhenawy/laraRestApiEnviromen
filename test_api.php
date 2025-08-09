<?php

// Test Register API
echo "Testing Register API...\n";

$url = 'http://127.0.0.1:8000/api/register';
$data = [
    'name' => 'ammar',
    'email' => 'ammar@gmail.com',
    'password' => 'maro123'
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Register Response:\n";
echo $result . "\n\n";

// Test Login API
echo "Testing Login API...\n";

$loginData = [
    'email' => 'ammar@gmail.com',
    'password' => 'maro123'
];

$loginOptions = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($loginData)
    ]
];

$loginContext = stream_context_create($loginOptions);
$loginResult = file_get_contents('http://127.0.0.1:8000/api/login', false, $loginContext);

echo "Login Response:\n";
echo $loginResult . "\n"; 