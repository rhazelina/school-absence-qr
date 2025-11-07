<?php

return [
    'qr' => [
        'default_expiration_minutes' => env('QR_DEFAULT_EXPIRATION', 5),
        'late_threshold_minutes' => env('ATTENDANCE_LATE_THRESHOLD', 10),
        'max_active_per_schedule' => env('ATTENDANCE_MAX_ACTIVE_QR', 1),
    ],
];
