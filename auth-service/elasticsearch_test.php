<?php

require_once __DIR__ . '/vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;

echo "🔍 Elasticsearch Test Script\n";
echo "==========================\n\n";

// Elasticsearch client oluştur
$client = ClientBuilder::create()
    ->setHosts(['http://localhost:9200'])
    ->build();

// 1. Bağlantı testi
echo "1. Elasticsearch bağlantı testi...\n";
try {
    // Basit info isteği ile test edelim
    $infoResponse = $client->info();
    $info = $infoResponse->asArray();
    
    echo "✅ Elasticsearch'e başarıyla bağlandı!\n";
    echo "   Cluster: " . $info['cluster_name'] . "\n";
    echo "   Version: " . $info['version']['number'] . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Bağlantı hatası: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 2. Cluster bilgisi
echo "2. Cluster bilgilerini al...\n";
try {
    $healthResponse = $client->cluster()->health();
    $health = $healthResponse->asArray();
    
    echo "   Cluster adı: " . $health['cluster_name'] . "\n";
    echo "   Durum: " . $health['status'] . "\n";
    echo "   Node sayısı: " . $health['number_of_nodes'] . "\n";
    echo "   Aktif shard'lar: " . $health['active_shards'] . "\n\n";
} catch (Exception $e) {
    echo "❌ Cluster bilgisi alınamadı: " . $e->getMessage() . "\n\n";
}

// 3. Test index oluştur ve document ekle
echo "3. Test index oluştur ve document ekle...\n";
$indexName = 'auth_service_test';
$documentId = 'test_' . uniqid();

try {
    // Test document
    $document = [
        'user_id' => 123,
        'action' => 'login',
        'ip_address' => '192.168.1.100',
        'timestamp' => date('c'),
        'success' => true,
        'user_agent' => 'Mozilla/5.0 Test Browser',
        'message' => 'User successfully logged in'
    ];

    // Document'i ekle
    $indexResponse = $client->index([
        'index' => $indexName,
        'id' => $documentId,
        'body' => $document
    ]);

    echo "✅ Document eklendi! ID: " . $documentId . "\n";
    echo "   Result: " . $indexResponse['result'] . "\n\n";

    // Index'i refresh et
    $client->indices()->refresh(['index' => $indexName]);

} catch (Exception $e) {
    echo "❌ Document eklenirken hata: " . $e->getMessage() . "\n\n";
}

// 4. Document ara
echo "4. Eklenen document'i ara...\n";
try {
    $searchResponse = $client->search([
        'index' => $indexName,
        'body' => [
            'query' => [
                'match' => [
                    'user_id' => 123
                ]
            ]
        ]
    ]);

    $hits = $searchResponse['hits']['hits'];
    echo "✅ Arama tamamlandı!\n";
    echo "   Bulunan document sayısı: " . count($hits) . "\n";
    
    if (count($hits) > 0) {
        echo "   İlk sonuç:\n";
        echo "     - ID: " . $hits[0]['_id'] . "\n";
        echo "     - User ID: " . $hits[0]['_source']['user_id'] . "\n";
        echo "     - Action: " . $hits[0]['_source']['action'] . "\n";
        echo "     - Timestamp: " . $hits[0]['_source']['timestamp'] . "\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "❌ Arama sırasında hata: " . $e->getMessage() . "\n\n";
}

// 5. Birden fazla document ekle (bulk insert)
echo "5. Bulk insert testi...\n";
try {
    $bulkBody = [];
    
    for ($i = 1; $i <= 5; $i++) {
        $bulkBody[] = [
            'index' => [
                '_index' => $indexName,
                '_id' => 'bulk_' . $i
            ]
        ];
        
        $bulkBody[] = [
            'user_id' => 100 + $i,
            'action' => 'page_view',
            'ip_address' => '192.168.1.' . (100 + $i),
            'timestamp' => date('c'),
            'page' => '/dashboard',
            'response_time' => rand(50, 500) . 'ms'
        ];
    }

    $bulkResponse = $client->bulk(['body' => $bulkBody]);
    
    if ($bulkResponse['errors']) {
        echo "⚠️  Bulk insert'te bazı hatalar var!\n";
    } else {
        echo "✅ Bulk insert başarılı! 5 document eklendi.\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "❌ Bulk insert hatası: " . $e->getMessage() . "\n\n";
}

// 6. Index istatistikleri
echo "6. Index istatistikleri...\n";
try {
    $client->indices()->refresh(['index' => $indexName]);
    
    $statsResponse = $client->indices()->stats(['index' => $indexName]);
    $stats = $statsResponse->asArray();
    
    $indexStats = $stats['indices'][$indexName];
    echo "✅ Index istatistikleri:\n";
    echo "   Document sayısı: " . $indexStats['total']['docs']['count'] . "\n";
    echo "   Index boyutu: " . round($indexStats['total']['store']['size_in_bytes'] / 1024, 2) . " KB\n\n";

} catch (Exception $e) {
    echo "❌ İstatistik alınamadı: " . $e->getMessage() . "\n\n";
}

// 7. Temizlik - test index'ini sil
echo "7. Test index'ini temizle...\n";
try {
    $deleteResponse = $client->indices()->delete(['index' => $indexName]);
    echo "✅ Test index silindi!\n\n";
} catch (Exception $e) {
    echo "⚠️  Index silinirken hata (zaten silinmiş olabilir): " . $e->getMessage() . "\n\n";
}

echo "🎉 Elasticsearch testi tamamlandı!\n";
echo "Kibana'ya erişmek için: http://localhost:5601\n";
