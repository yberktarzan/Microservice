import Fastify from 'fastify';
import dotenv from 'dotenv';
import helmet from '@fastify/helmet';
import rateLimit from '@fastify/rate-limit';
import fastifyJwt from '@fastify/jwt';
import httpProxy from '@fastify/http-proxy';

dotenv.config({ path: process.cwd() + '/../.env' });

const app = Fastify({
    logger: {
        transport: { target: 'pino-pretty' }
    }
});

const {
    JWT_SECRET = 'devsecret',
    AUTH_BASE_URL = 'http://localhost:8000',
    ADMIN_BASE_URL = 'http://localhost:8001'
} = process.env;

// 🔒 Güvenlik ve limitler
await app.register(helmet);
await app.register(rateLimit, { max: 100, timeWindow: '1 minute' });
await app.register(fastifyJwt, { secret: JWT_SECRET });

// 🟢 Health check
app.get('/health', async () => ({ ok: true, service: 'gateway' }));

// Auth guard
async function authGuard(req, reply) {
    try {
        await req.jwtVerify();
    } catch (err) {
        return reply.code(401).send({ message: 'Unauthorized' });
    }
}

// --- Auth servis proxy (login serbest, me korumalı) ---
await app.register(httpProxy, {
    upstream: AUTH_BASE_URL,
    prefix: '/api/login',
    rewritePrefix: '/api/login'
});

app.register(async (instance) => {
    instance.addHook('preHandler', authGuard);
    await instance.register(httpProxy, {
        upstream: AUTH_BASE_URL,
        prefix: '/api/me',
        rewritePrefix: '/api/me'
    });
});

// --- Admin servis proxy (tamamen korumalı) ---
app.register(async (instance) => {
    instance.addHook('preHandler', authGuard);
    await instance.register(httpProxy, {
        upstream: ADMIN_BASE_URL,
        prefix: '/api/orders',
        rewritePrefix: '/api/orders'
    });
});

const port = process.env.PORT || 8080;
app.listen({ port, host: '0.0.0.0' }).then(() => {
    app.log.info(`🚀 Gateway running at http://localhost:${port}`);
});
