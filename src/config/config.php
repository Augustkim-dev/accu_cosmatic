<?php
// 환경변수(.env → compose에서 주입)를 읽되, 없으면 안전한 기본값
function env(string $key, $default = null) {
    $v = getenv($key);
    return ($v === false || $v === '') ? $default : $v;
}

return [
    'db' => [
        'host'    => env('DB_HOST', 'arno_db'),
        'port'    => env('DB_PORT', '3306'),
        'name'    => env('DB_NAME', 'accu_cosmetic'),
        'user'    => env('DB_USER', 'accu'),
        'pass'    => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'env'   => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', 'false') === 'true',
        'key'   => env('APP_KEY', 'change-me'),
    ],
];
