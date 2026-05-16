<?php

return [
    /*
    | Các đường dẫn được áp dụng cấu hình CORS
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    | Cho phép tất cả các phương thức (GET, POST, PUT, DELETE...)
    */
    'allowed_methods' => ['*'],

    /*
    | Quan trọng: Cho phép các nguồn từ Frontend gọi vào
    | Nếu vẫn lỗi, hãy đổi thành ['*'] để mở hoàn toàn
    */
    'allowed_origins' => ['http://localhost:5173', 'http://localhost:5174', 'http://127.0.0.1:5173', 'http://127.0.0.1:5174'],

    'allowed_origins_patterns' => [],

    /*
    | Cho phép tất cả Headers để tránh lỗi Accept hoặc Authorization
    */
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
    | Bắt buộc là true nếu bạn dùng Laravel Sanctum để lưu Cookie/Session
    */
    'supports_credentials' => true,
];