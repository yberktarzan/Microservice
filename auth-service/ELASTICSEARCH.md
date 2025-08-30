# 🔍 Elasticsearch Integration

This document describes the Elasticsearch integration in the Auth Service microservice. This integration provides comprehensive logging, monitoring, and analytics capabilities for authentication events and API responses.

## 📋 Overview

The Elasticsearch integration consists of:

- **Auth Event Logging**: Tracks login attempts, registrations, suspicious activities
- **API Response Logging**: Monitors all API responses with performance metrics
- **Security Analytics**: Identifies patterns and anomalies in authentication behavior
- **Management Commands**: Easy-to-use Artisan commands for maintenance

## 🏗️ Architecture

```
Auth Service
├── Services/
│   └── Elasticsearch/
│       ├── ElasticsearchService.php      # Core Elasticsearch client
│       └── AuthEventLogger.php           # Auth-specific event logging
├── Logging/
│   └── ApiResponseLogger.php             # Enhanced with Elasticsearch
└── Console/Commands/
    └── ElasticsearchCommand.php          # Management commands
```

## 📊 Data Structure

### Auth Events Index (`auth-events`)
```json
{
  "event_type": "login_success|login_failed|user_registered|logout|password_changed|suspicious_activity",
  "user_id": 123,
  "email": "user@example.com",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "timestamp": "2025-08-30T22:43:15.123Z",
  "context": {
    "device": "MacBook Pro",
    "location": "Istanbul, TR"
  },
  "request_data": {
    "method": "POST",
    "endpoint": "/api/auth/login",
    "url": "https://auth.example.com/api/auth/login"
  }
}
```

### API Responses Index (`auth-api-responses`)
```json
{
  "success": true,
  "status_code": 200,
  "message": "Login successful",
  "response_time": 245.67,
  "memory_usage": 8388608,
  "timestamp": "2025-08-30T22:43:15.123Z",
  "request": {
    "method": "POST",
    "endpoint": "/api/auth/login",
    "ip": "192.168.1.100",
    "user_agent": "Mozilla/5.0..."
  },
  "user": {
    "id": 123,
    "email": "user@example.com"
  }
}
```

## 🚀 Quick Start

### 1. Environment Setup
```bash
# Add to .env
ELASTICSEARCH_HOST=http://localhost:9200
ELASTICSEARCH_ENABLED=true
ELASTICSEARCH_API_INDEX=auth-api-responses
ELASTICSEARCH_AUTH_INDEX=auth-events
ELASTICSEARCH_ERROR_INDEX=auth-errors
```

### 2. Start Services
```bash
# Start Elasticsearch and Kibana
docker-compose up -d elasticsearch kibana

# Verify Elasticsearch is running
curl http://localhost:9200
```

### 3. Initialize Indices
```bash
# Check health
php artisan elasticsearch:manage health

# Create indices
php artisan elasticsearch:manage create

# View indices
php artisan elasticsearch:manage indices
```

### 4. Test Integration
```bash
# Run integration test
php elasticsearch_integration_test.php

# Check data in Elasticsearch
curl "http://localhost:9200/auth-events/_search?pretty"
curl "http://localhost:9200/auth-api-responses/_search?pretty"
```

## 🔧 Usage Examples

### Manual Event Logging
```php
use App\Services\Elasticsearch\AuthEventLogger;

$authLogger = app(AuthEventLogger::class);

// Log successful login
$authLogger->logLoginSuccess(123, 'user@example.com', [
    'device' => 'iPhone 12',
    'location' => 'Istanbul'
]);

// Log failed login
$authLogger->logLoginFailed('hacker@evil.com', 'invalid_password', [
    'attempts' => 5,
    'ip_blocked' => true
]);

// Log suspicious activity
$authLogger->logSuspiciousActivity('brute_force', 'attacker@bad.com', [
    'pattern' => 'automated_requests',
    'severity' => 'high'
]);
```

### API Response Logging (Automatic)
API responses are automatically logged when using the `ApiResponse` trait:

```php
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(LoginRequest $request)
    {
        // This response will be automatically logged to Elasticsearch
        return $this->successResponse(
            data: ['token' => $token],
            message: 'Login successful',
            shouldLog: true  // Enable Elasticsearch logging
        );
    }
}
```

## 📈 Analytics Queries

### Security Analytics
```bash
# Failed login attempts in last hour
curl -X GET "localhost:9200/auth-events/_search" -H 'Content-Type: application/json' -d'
{
  "query": {
    "bool": {
      "must": [
        {"term": {"event_type": "login_failed"}},
        {"range": {"timestamp": {"gte": "now-1h"}}}
      ]
    }
  }
}'

# Top IP addresses by request count
curl -X GET "localhost:9200/auth-api-responses/_search" -H 'Content-Type: application/json' -d'
{
  "size": 0,
  "aggs": {
    "top_ips": {
      "terms": {"field": "request.ip", "size": 10}
    }
  }
}'
```

### Performance Analytics
```bash
# Average response times by endpoint
curl -X GET "localhost:9200/auth-api-responses/_search" -H 'Content-Type: application/json' -d'
{
  "size": 0,
  "aggs": {
    "endpoints": {
      "terms": {"field": "request.endpoint"},
      "aggs": {
        "avg_response_time": {"avg": {"field": "response_time"}}
      }
    }
  }
}'
```

## 🛠️ Management Commands

```bash
# Health check
php artisan elasticsearch:manage health

# List indices with stats
php artisan elasticsearch:manage indices

# Create/verify indices
php artisan elasticsearch:manage create

# Clean old data (30 days by default)
php artisan elasticsearch:manage clean --days=30 --force

# Help
php artisan elasticsearch:manage help
```

## 🔒 Security Features

### Data Sanitization
- Sensitive headers (Authorization, Cookie) are automatically redacted
- Password fields are never logged
- API keys and tokens are masked

### Rate Limiting Detection
- Automatic detection of suspicious request patterns
- IP-based analysis for brute force attempts
- Configurable thresholds for security alerts

### Compliance
- GDPR-compliant data retention policies
- Configurable data anonymization
- Audit trail for all authentication events

## 📊 Kibana Dashboard

Access Kibana at `http://localhost:5601` to create dashboards for:

- **Authentication Success Rate**: Monitor login success/failure ratios
- **Geographic Analysis**: Visualize login locations on world map
- **Performance Metrics**: Track API response times and error rates
- **Security Monitoring**: Detect suspicious activities and patterns

### Sample Kibana Visualizations

1. **Login Success Rate Timeline**
   - Index: `auth-events`
   - Filter: `event_type:login_success OR event_type:login_failed`
   - Aggregation: Date histogram with success/failure counts

2. **API Response Time Heatmap**
   - Index: `auth-api-responses`
   - Metrics: Average response_time
   - Buckets: Terms aggregation on endpoint

3. **Geographic Login Map**
   - Index: `auth-events`
   - Filter: `event_type:login_success`
   - Coordinates: `geo.country` field

## 🚨 Monitoring & Alerts

### Key Metrics to Monitor
- Failed login attempts per hour
- Average API response times
- Error rate percentage
- Unique IP addresses per day
- Suspicious activity count

### Alert Conditions
- Failed login rate > 10% in 5 minutes
- Response time > 2 seconds for 95th percentile
- Error rate > 5% in 10 minutes
- Suspicious activity detected

## 🔧 Configuration

### Elasticsearch Settings
```php
// config/services.php
'elasticsearch' => [
    'host' => env('ELASTICSEARCH_HOST', 'http://localhost:9200'),
    'enabled' => env('ELASTICSEARCH_ENABLED', true),
    'api_index' => env('ELASTICSEARCH_API_INDEX', 'auth-api-responses'),
    'auth_index' => env('ELASTICSEARCH_AUTH_INDEX', 'auth-events'),
    'error_index' => env('ELASTICSEARCH_ERROR_INDEX', 'auth-errors'),
    'retention_days' => env('ELASTICSEARCH_RETENTION_DAYS', 30),
]
```

### Logging Configuration
```php
// config/logging.php
'elasticsearch' => [
    'driver' => 'monolog',
    'handler' => ElasticsearchHandler::class,
    'with' => [
        'client' => ClientBuilder::create()
            ->setHosts([env('ELASTICSEARCH_HOST', 'http://localhost:9200')])
            ->build(),
        'options' => ['index' => 'auth-service-logs'],
    ],
    'formatter' => ElasticsearchFormatter::class,
],
```

## 📝 Best Practices

1. **Index Management**
   - Use time-based indices for large volumes
   - Implement index lifecycle management (ILM)
   - Regular cleanup of old data

2. **Performance**
   - Batch operations when possible
   - Use async logging for high-traffic endpoints
   - Monitor Elasticsearch cluster health

3. **Security**
   - Encrypt data in transit and at rest
   - Implement proper access controls
   - Regular security audits

4. **Monitoring**
   - Set up proper alerting rules
   - Monitor cluster metrics
   - Regular backup procedures

## 🐛 Troubleshooting

### Common Issues

**Elasticsearch not available**
```bash
# Check if Elasticsearch is running
curl http://localhost:9200

# Check logs
docker logs elasticsearch

# Restart services
docker-compose restart elasticsearch
```

**Index mapping conflicts**
```bash
# Delete and recreate indices
curl -X DELETE "localhost:9200/auth-events"
php artisan elasticsearch:manage create
```

**Performance issues**
```bash
# Check cluster health
curl "localhost:9200/_cluster/health?pretty"

# Monitor index size
curl "localhost:9200/_cat/indices?v&s=store.size:desc"
```

## 📚 Resources

- [Elasticsearch Documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/)
- [Kibana User Guide](https://www.elastic.co/guide/en/kibana/current/)
- [Laravel Elasticsearch Package](https://github.com/elastic/elasticsearch-php)

---

This integration provides a robust foundation for monitoring, analytics, and security in your authentication microservice. The implementation follows best practices and is designed to scale with your application's growth.
