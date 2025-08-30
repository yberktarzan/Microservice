import httpProxy from '@fastify/http-proxy';
import { config } from '../config.js';
import { authGuard } from '../middlewares/authGuard.js';
import { exampleGuard } from '../middlewares/serviceGuards/exampleGuard.js';

/**
 * @fileoverview Central place to configure service proxies.
 * Keeps server.js clean and extendable.
 */

/**
 * Registers all service proxies and applies appropriate guards.
 * @param {import('fastify').FastifyInstance} app
 */
export async function registerProxies(app) {
    // AUTH routes
    await app.register(httpProxy, {
        upstream: config.services.auth,
        prefix: '/api/login',
        rewritePrefix: '/api/login',
    });

    app.register(async (instance) => {
        instance.addHook('preHandler', authGuard);
        await instance.register(httpProxy, {
            upstream: config.services.auth,
            prefix: '/api/me',
            rewritePrefix: '/api/me',
        });
    });

    // ADMIN routes (protected by central Auth Service)
    app.register(async (instance) => {
        instance.addHook('preHandler', authGuard);
        await instance.register(httpProxy, {
            upstream: config.services.admin,
            prefix: '/api/orders',
            rewritePrefix: '/api/orders',
        });
    });

    // EXAMPLE routes (protected by its own guard)
    app.register(async (instance) => {
        instance.addHook('preHandler', exampleGuard);
        await instance.register(httpProxy, {
            upstream: config.services.example,
            prefix: '/api/example',
            rewritePrefix: '/api/example',
        });
    });
}
