/**
 * @fileoverview Example custom guard for a microservice.
 * This service uses its own authentication mechanism,
 * independent of the central Auth microservice.
 *
 * Replace this file with a real guard (e.g. paymentsGuard.js) when needed.
 */

/**
 * Example guard implementation (API Key based).
 * @param {import('fastify').FastifyRequest} req
 * @param {import('fastify').FastifyReply} reply
 */
export async function exampleGuard(req, reply) {
    const apiKey = req.headers['x-api-key'];

    // For demonstration purposes, we validate against a static key.
    // In a real-world scenario, this key should come from environment variables
    // or a secret manager (e.g., Vault, AWS Secrets Manager).
    if (!apiKey || apiKey !== 'demo-secret-key') {
        return reply.code(401).send({ message: 'Unauthorized (Example Service)' });
    }

    // If validation passes, continue with the request.
}
