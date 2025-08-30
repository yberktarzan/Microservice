/**
 * @fileoverview Health check endpoint to ensure service availability.
 */

/**
 * @param {import('fastify').FastifyInstance} app
 */
export async function healthRoute(app) {
    app.get('/health', async () => ({
        ok: true,
        service: 'gateway',
        timestamp: new Date().toISOString(),
    }));
}
