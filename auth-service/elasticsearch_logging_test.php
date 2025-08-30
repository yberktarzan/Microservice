<?php

require_once __DIR__ . '/vendor/autoload.php';

// Laravel bootstrap (basit test için)
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Log;

echo "🔐 Auth Service Elasticsearch Logging Test\n";
echo "==========================================\n\n";

echo "📝 Test loglarını Elasticsearch'e gönderiliyor...\n\n";

// Test log mesajları
$testLogs = [
    [
        'level' => 'info',
        'message' => 'User login successful',
        'context' => [
            'user_id' => 2001,
            'username' => 'test.user@example.com',
            'ip_address' => '127.0.0.1',
            'action' => 'login',
            'response_time' => 123
        ]
    ],
    [
        'level' => 'warning',
        'message' => 'Multiple failed login attempts detected',
        'context' => [
            'username' => 'suspicious.user@example.com',
            'ip_address' => '203.0.113.45',
            'attempt_count' => 5,
            'action' => 'login_failed'
        ]
    ],
    [
        'level' => 'error',
        'message' => 'Authentication service timeout',
        'context' => [
            'error_code' => 'AUTH_TIMEOUT',
            'response_time' => 5000,
            'action' => 'token_validation'
        ]
    ]
];

foreach ($testLogs as $logData) {
    try {
        switch ($logData['level']) {
            case 'info':
                Log::channel('elasticsearch')->info($logData['message'], $logData['context']);
                echo "✅ INFO log gönderildi: " . $logData['message'] . "\n";
                break;
            case 'warning':
                Log::channel('elasticsearch')->warning($logData['message'], $logData['context']);
                echo "⚠️  WARNING log gönderildi: " . $logData['message'] . "\n";
                break;
            case 'error':
                Log::channel('elasticsearch')->error($logData['message'], $logData['context']);
                echo "❌ ERROR log gönderildi: " . $logData['message'] . "\n";
                break;
        }
        
        // Kısa bir bekleme
        usleep(100000); // 0.1 saniye
        
    } catch (Exception $e) {
        echo "❌ Log gönderilirken hata: " . $e->getMessage() . "\n";
    }
}

echo "\n🎯 Elasticsearch logging testi tamamlandı!\n";
echo "💡 Logları kontrol etmek için Laravel log dosyalarını da kontrol edebilirsiniz.\n";
