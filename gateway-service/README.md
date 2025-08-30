# Gateway Service 🚀

API Gateway for microservice architecture. Routes and protects requests to downstream services.

## 🏗️ Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Client Apps   │    │   Load Balancer │    │   Gateway       │
│                 │───▶│                 │───▶│   (This Service)│
│ Web/Mobile/API  │    │   (Optional)    │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                                       │
                       ┌───────────────────────────────┼───────────────────────────────┐
                       │                               │                               │
                       ▼                               ▼                               ▼
            ┌─────────────────┐            ┌─────────────────┐            ┌─────────────────┐
            │   Auth Service  │            │  Admin Service  │            │  Other Services │
            │   Port: 8000    │            │   Port: 8001    │            │   Port: 9000+   │
            │                 │            │                 │            │                 │
            └─────────────────┘            └─────────────────┘            └─────────────────┘
```

## 🚀 Quick Start

### Prerequisites
- Node.js 18+ 
- npm or yarn

### Installation

```bash
# Clone the repository
git clone <your-repo-url>
cd gateway

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Edit configuration
nano .env

# Start development server
npm run dev
```

### Production

```bash
# Build for production
npm run build

# Start production server
npm start
```

## 📦 Environment Variables

Create a `.env` file in the project root:

```bash
# Server Configuration
PORT=8080
NODE_ENV=development

# JWT Configuration
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production

# Microservice URLs
AUTH_BASE_URL=http://localhost:8000
ADMIN_BASE_URL=http://localhost:8001

# Security Configuration
RATE_LIMIT_MAX=200
RATE_LIMIT_WINDOW=1 minute

# CORS Configuration
CORS_ORIGIN=http://localhost:3000

# Logging
LOG_LEVEL=info
```

## 🛡️ Security Features

- **JWT Authentication**: Token-based authentication with configurable secret
- **Rate Limiting**: Configurable request rate limiting per client
- **CORS Protection**: Cross-origin request filtering
- **Helmet**: Security headers protection
- **Input Validation**: Request/response validation
- **Error Handling**: Centralized error handling with proper logging

## 🔄 API Routes

### Public Routes
- `GET /health` - Health check endpoint

### Auth Service Routes
- `POST /api/login` - User login (public)
- `GET /api/me` - Get current user (protected)

### Admin Service Routes  
- `GET /api/orders` - Orders management (protected)

### Custom Service Routes
- `GET /api/example` - Example service (custom auth)

## 🔧 Adding New Services

1. **Add service URL to config**:
```javascript
// src/config.js
services: {
  auth: process.env.AUTH_BASE_URL || 'http://localhost:8000',
  admin: process.env.ADMIN_BASE_URL || 'http://localhost:8001',
  newService: process.env.NEW_SERVICE_URL || 'http://localhost:9002', // Add this
}
```

2. **Create service-specific guard (if needed)**:
```javascript
// src/middlewares/serviceGuards/newServiceGuard.js
export async function newServiceGuard(req, reply) {
  // Custom authentication logic
}
```

3. **Register proxy routes**:
```javascript
// src/plugins/proxy.js
app.register(async (instance) => {
  instance.addHook('preHandler', authGuard); // or custom guard
  await instance.register(httpProxy, {
    upstream: config.services.newService,
    prefix: '/api/newservice',
    rewritePrefix: '/api/newservice',
  });
});
```

## 🏃‍♂️ Development

### Scripts
```bash
npm run dev         # Start development server with hot reload
npm run start       # Start production server
npm run test        # Run tests
npm run test:watch  # Run tests in watch mode
npm run lint        # Lint code
npm run lint:fix    # Fix linting issues
```

### Docker Support

```bash
# Build image
docker build -t gateway .

# Run container
docker run -p 8080:8080 --env-file .env gateway

# Or use docker-compose
docker-compose up
```

## 📊 Monitoring & Observability

- **Health Checks**: `/health` endpoint for service discovery
- **Structured Logging**: JSON formatted logs with request correlation
- **Error Tracking**: Centralized error handling and reporting
- **Metrics**: Request/response metrics (coming soon)

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📋 API Documentation

### Health Check
```http
GET /health
```

**Response:**
```json
{
  "ok": true,
  "service": "gateway",
  "timestamp": "2024-03-15T10:30:00.000Z"
}
```

### Authentication
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "email": "user@example.com"
  }
}
```

## 🔍 Troubleshooting

### Common Issues

**Port Already in Use**
```bash
lsof -ti:8080 | xargs kill -9
```

**JWT Verification Fails**
- Check JWT_SECRET environment variable
- Ensure token is sent in Authorization header: `Bearer <token>`

**Service Connection Refused**
- Verify downstream service URLs in .env
- Check if downstream services are running

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Fastify](https://fastify.io/) - Fast and low overhead web framework
- [Pino](https://getpino.io/) - Fast JSON logger
- Built with ❤️ for microservice architecture
