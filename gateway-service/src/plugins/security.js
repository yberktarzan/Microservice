import helmet from '@fastify/helmet';
import rateLimit from '@fastify/rate-limit';
import cors from '@fastify/cors';
import { config } from '../config.js';

/**
 * @fileoverview Security bootstrap for the Gateway.
 * 
 * Uses centralized config values for rate limiting and CORS.
 * JWT validation is handled manually in auth guards to support Laravel tokens.
 */

/**
 * Registers security-related plugins into the Fastify application.
 *
 * @async
 * @function registerSecurity
 * @param {import('fastify').FastifyInstance} app - The Fastify application instance.
 * @returns {Promise<void>} Resolves when all plugins are successfully registered.
 */
export async function registerSecurity(app) {
    // CORS configuration
    await app.register(cors, config.security.cors);

    // Secure HTTP headers
    await app.register(helmet, {
        contentSecurityPolicy: config.nodeEnv === 'production',
    });

    // Global rate limit (configurable via .env)
    await app.register(rateLimit, {
        max: config.security.rateLimit.max,
        timeWindow: config.security.rateLimit.timeWindow,
        standardHeaders: true,
        legacyHeaders: false,
    });

    // Note: JWT validation is handled manually in auth guards
    // to support Laravel JWT tokens with custom validation logic
}
