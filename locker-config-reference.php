<?php

return [
    // Prototype admin login. Set strong values in .env and never commit .env.
    'admin_username' => env('LOCKER_ADMIN_USERNAME', 'admin'),
    'admin_password' => env('LOCKER_ADMIN_PASSWORD'),

    // Single-device Wokwi prototype. Use a unique key per physical device in production.
    'device_id' => env('LOCKER_DEVICE_ID', 'ESP32_01'),
    'device_key' => env('LOCKER_DEVICE_KEY'),
];
