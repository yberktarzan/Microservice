import Fastify from 'fastify';
import { logger } from './utils/logger.js';
import { config } from './config.js';
import { registerSecurity } from './plugins/security.js';
import { registerErrorHandler } from './plugins/errorHandler.js';
import { registerProxies } from './plugins/proxy.js';
import { healthRoute } from './routes/health.js';

/**
 * @fileoverview Main entrypoint for the Gateway server.
 *
 * Responsibilities:
 * - Bootstraps a Fastify application instance with centralized logging.
 * - Registers all cross-cutting concerns: security, error handling, health checks, service proxies.
 * - Starts the HTTP server and listens for incoming traffic.
 *
*/

/**
 * Builds and configures the Fastify server instance.
 *
 * @async
 * @function buildServer
 * @returns {Promise<import('fastify').FastifyInstance>} The configured Fastify application.
 *
 * @example
 * const app = await buildServer();
 * await app.listen({ port: config.port });
 */
async function buildServer() {
    const app = Fastify({ 
        logger,
        trustProxy: true,
        requestIdHeader: 'x-request-id',
        requestIdLogLabel: 'reqId',
    });

    // Error handling (register first)
    await registerErrorHandler(app);

    // Security middleware (Helmet, CORS, RateLimit, JWT)
    await registerSecurity(app);

    // Health check endpoint (/health)
    await healthRoute(app);

    // Reverse proxy routes for microservices
    await registerProxies(app);

    return app;
}

const app = await buildServer();

/**
 * Starts the Fastify server.
 * Listens on the configured port and logs startup information.
 */
const startServer = async () => {
    try {
        await app.listen({ 
            port: config.port, 
            host: '0.0.0.0' 
        });
        
        logger.info({
            port: config.port,
            nodeEnv: config.nodeEnv,
            services: config.services,
        }, 'Gateway server started successfully');
        
    } catch (error) {
        logger.error(error, 'Failed to start server');
        process.exit(1);
    }
};

// Graceful shutdown
const gracefulShutdown = async (signal) => {
    logger.info(`Received ${signal}, shutting down gracefully`);
    
    try {
        await app.close();
        logger.info('Server closed successfully');
        process.exit(0);
    } catch (error) {
        logger.error(error, 'Error during server shutdown');
        process.exit(1);
    }
};

// Handle shutdown signals
process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));
process.on('SIGINT', () => gracefulShutdown('SIGINT'));

// Start the server
startServer();
