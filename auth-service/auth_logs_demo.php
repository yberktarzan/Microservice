<?php

require_once __DIR__ . '/vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

echo "🔍 Auth Service Elasticsearch Demo\n";
echo "===================================\n\n";

$client = ClientBuilder::create()
    ->setHosts(['http://localhost:9200'])
    ->build();

$indexName = 'auth_service_logs';

echo "📝 Auth Service için örnek loglar oluşturuluyor...\n\n";

// Gerçekçi auth service logları
$authLogs = [
    [
        'id' => 'auth_' . uniqid(),
        'user_id' => 1001,
        'username' => 'john.doe@example.com',
        'action' => 'login_success',
        'ip_address' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
        'timestamp' => date('c', strtotime('-2 hours')),
        'response_time' => 245,
        'success' => true
    ],
    [
        'id' => 'auth_' . uniqid(),
        'user_id' => 1002,
        'username' => 'jane.smith@example.com',
        'action' => 'login_failed',
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'timestamp' => date('c', strtotime('-1 hour')),
        'response_time' => 150,
        'success' => false,
        'error_reason' => 'invalid_password'
    ],
    [
        'id' => 'auth_' . uniqid(),
        'user_id' => 1003,
        'username' => 'admin@example.com',
        'action' => 'token_refresh',
        'ip_address' => '192.168.1.102',
        'user_agent' => 'PostmanRuntime/7.32.3',
        'timestamp' => date('c', strtotime('-30 minutes')),
        'response_time' => 89,
        'success' => true
    ],
    [
        'id' => 'auth_' . uniqid(),
        'user_id' => 1001,
        'username' => 'john.doe@example.com',
        'action' => 'logout',
        'ip_address' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
        'timestamp' => date('c', strtotime('-15 minutes')),
        'response_time' => 45,
        'success' => true
    ],
    [
        'id' => 'auth_' . uniqid(),
        'user_id' => null,
        'username' => 'hacker@malicious.com',
        'action' => 'brute_force_attempt',
        'ip_address' => '185.234.72.45',
        'user_agent' => 'curl/7.68.0',
        'timestamp' => date('c', strtotime('-5 minutes')),
        'response_time' => 1200,
        'success' => false,
        'error_reason' => 'rate_limit_exceeded'
    ]
];

// Bulk insert
$bulkBody = [];
foreach ($authLogs as $log) {
    $bulkBody[] = [
        'index' => [
            '_index' => $indexName,
            '_id' => $log['id']
        ]
    ];
    unset($log['id']); // ID'yi body'den çıkar
    $bulkBody[] = $log;
}

try {
    $bulkResponse = $client->bulk(['body' => $bulkBody]);
    
    if ($bulkResponse['errors']) {
        echo "⚠️  Bazı loglar eklenirken hata oluştu!\n";
    } else {
        echo "✅ " . count($authLogs) . " auth log başarıyla eklendi!\n\n";
    }
    
    // Index'i refresh et
    $client->indices()->refresh(['index' => $indexName]);
    
} catch (Exception $e) {
    echo "❌ Log ekleme hatası: " . $e->getMessage() . "\n";
    exit(1);
}

// Bazı sorgular yapalım
echo "🔍 Auth Service Log Analizleri:\n";
echo "===============================\n\n";

// 1. Başarısız login denemeleri
echo "1. 📊 Başarısız Login Denemeleri:\n";
try {
    $response = $client->search([
        'index' => $indexName,
        'body' => [
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['success' => false]],
                        ['wildcard' => ['action' => '*login*']]
                    ]
                ]
            ],
            'sort' => [
                ['timestamp' => ['order' => 'desc']]
            ]
        ]
    ]);
    
    $hits = $response['hits']['hits'];
    echo "   Toplam başarısız login: " . count($hits) . "\n";
    
    foreach ($hits as $hit) {
        $source = $hit['_source'];
        echo "   - " . $source['username'] . " (" . $source['ip_address'] . ") - " . 
             ($source['error_reason'] ?? 'bilinmeyen hata') . "\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "   ❌ Sorgu hatası: " . $e->getMessage() . "\n\n";
}

// 2. Kullanıcı aktivite özeti
echo "2. 👤 Kullanıcı Aktiviteleri:\n";
try {
    $response = $client->search([
        'index' => $indexName,
        'body' => [
            'size' => 0,
            'aggs' => [
                'user_actions' => [
                    'terms' => [
                        'field' => 'username.keyword',
                        'size' => 10
                    ],
                    'aggs' => [
                        'actions' => [
                            'terms' => [
                                'field' => 'action.keyword'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]);
    
    $buckets = $response['aggregations']['user_actions']['buckets'];
    
    foreach ($buckets as $bucket) {
        echo "   👤 " . $bucket['key'] . " (" . $bucket['doc_count'] . " aktivite)\n";
        foreach ($bucket['actions']['buckets'] as $action) {
            echo "      - " . $action['key'] . ": " . $action['doc_count'] . "x\n";
        }
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "   ❌ Agregasyon hatası: " . $e->getMessage() . "\n\n";
}

// 3. Son 1 saatteki aktiviteler
echo "3. ⏰ Son 1 Saatteki Aktiviteler:\n";
try {
    $response = $client->search([
        'index' => $indexName,
        'body' => [
            'query' => [
                'range' => [
                    'timestamp' => [
                        'gte' => date('c', strtotime('-1 hour'))
                    ]
                ]
            ],
            'sort' => [
                ['timestamp' => ['order' => 'desc']]
            ]
        ]
    ]);
    
    $hits = $response['hits']['hits'];
    echo "   Son 1 saatte " . count($hits) . " aktivite:\n";
    
    foreach ($hits as $hit) {
        $source = $hit['_source'];
        $time = date('H:i:s', strtotime($source['timestamp']));
        $status = $source['success'] ? '✅' : '❌';
        echo "   $status $time - " . $source['action'] . " (" . $source['username'] . ")\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "   ❌ Zaman sorgusu hatası: " . $e->getMessage() . "\n\n";
}

// 4. IP bazlı analiz
echo "4. 🌍 IP Adresi Analizi:\n";
try {
    $response = $client->search([
        'index' => $indexName,
        'body' => [
            'size' => 0,
            'aggs' => [
                'ip_stats' => [
                    'terms' => [
                        'field' => 'ip_address.keyword',
                        'size' => 10
                    ],
                    'aggs' => [
                        'avg_response_time' => [
                            'avg' => [
                                'field' => 'response_time'
                            ]
                        ],
                        'success_rate' => [
                            'avg' => [
                                'field' => 'success'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]);
    
    $buckets = $response['aggregations']['ip_stats']['buckets'];
    
    foreach ($buckets as $bucket) {
        $successRate = round($bucket['success_rate']['value'] * 100, 1);
        $avgResponseTime = round($bucket['avg_response_time']['value'], 0);
        
        echo "   🌍 " . $bucket['key'] . " (" . $bucket['doc_count'] . " istek)\n";
        echo "      - Başarı oranı: %" . $successRate . "\n";
        echo "      - Ortalama yanıt süresi: " . $avgResponseTime . "ms\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "   ❌ IP analizi hatası: " . $e->getMessage() . "\n\n";
}

echo "🎯 Elasticsearch sorguları tamamlandı!\n";
echo "📋 Tüm logları görmek için: curl \"http://localhost:9200/$indexName/_search?pretty\"\n";
echo "🗑️  Index'i silmek için: curl -X DELETE \"http://localhost:9200/$indexName\"\n";
