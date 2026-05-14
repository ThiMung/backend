<?php
return [
    // Cho phép cả 2 URL của Frontend gọi API 
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', '*'],
    'allowed_origins' => [
        'http://localhost:5173', // Cổng cho Organizer
        'http://localhost:5174'  // Cổng cho Attendee
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
