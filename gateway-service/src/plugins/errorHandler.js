import { logger } from '../utils/logger.js';

/**
 * @fileoverview Global error handler for the Gateway.
 * Provides consistent error responses and logging.
 */

/**
 * Registers global error handling for the Fastify application.
 * 
 * @param {import('fastify').FastifyInstance} app - The Fastify application instance.
 */
export async function registerErrorHandler(app) {
    // Global error handler
    app.setErrorHandler((error, request, reply) => {
        // Log the error with request context
        logger.error({
            error: {
                message: error.message,
                stack: error.stack,
                statusCode: error.statusCode,
            },
            request: {
                method: request.method,
                url: request.url,
                headers: request.headers,
                ip: request.ip,
            },
        }, 'Request error occurred');

        // Don't expose internal errors in production
        const isDevelopment = process.env.NODE_ENV !== 'production';

        // Determine status code
        const statusCode = error.statusCode || 500;

        // Prepare error response
        const errorResponse = {
            error: true,
            message: statusCode >= 500 && !isDevelopment 
                ? 'Internal Server Error' 
                : error.message,
            statusCode,
            ...(isDevelopment && { 
                stack: error.stack,
                details: error.validation || undefined,
            }),
        };

        reply.status(statusCode).send(errorResponse);
    });

    // Not found handler
    app.setNotFoundHandler((request, reply) => {
        logger.warn({
            request: {
                method: request.method,
                url: request.url,
                ip: request.ip,
            },
        }, 'Route not found');

        reply.status(404).send({
            error: true,
            message: 'Route not found',
            statusCode: 404,
        });
    });
}
