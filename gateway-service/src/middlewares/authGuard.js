/**
 * @fileoverview Gateway guard for the central Auth microservice (Laravel).
 * Verifies JWT tokens issued by Laravel Auth Service.
 * This guard validates tokens using shared secret and Laravel-specific claims.
 */

import jwt from 'jsonwebtoken';
import { config } from '../config.js';

/**
 * Middleware: verifies JWT from Authorization header issued by Laravel Auth Service.
 * @param {import('fastify').FastifyRequest} req
 * @param {import('fastify').FastifyReply} reply
 */
export async function authGuard(req, reply) {
    try {
        // Extract token from Authorization header
        const authHeader = req.headers.authorization;
        if (!authHeader || !authHeader.startsWith('Bearer ')) {
            return reply.code(401).send({ 
                error: 'Unauthorized',
                message: 'Missing or invalid Authorization header'
            });
        }

        const token = authHeader.substring(7); // Remove 'Bearer ' prefix

        // Verify JWT token with Laravel's secret and settings
        const decoded = jwt.verify(token, config.auth.jwtSecret, {
            issuer: config.auth.jwtIssuer,
            audience: config.auth.jwtAudience,
            algorithms: ['HS256'] // Laravel JWT typically uses HS256
        });

        // Add user info to request for downstream services
        req.user = {
            id: decoded.sub || decoded.user_id, // Laravel might use 'sub' or 'user_id'
            email: decoded.email,
            roles: decoded.roles || [],
            permissions: decoded.permissions || [],
            // Add other Laravel-specific claims as needed
        };

        // Add original token for forwarding to other services if needed
        req.token = token;

    } catch (err) {
        req.log.warn({ error: err.message }, 'JWT verification failed');
        
        // Handle different JWT errors
        let message = 'Invalid token';
        if (err.name === 'TokenExpiredError') {
            message = 'Token expired';
        } else if (err.name === 'JsonWebTokenError') {
            message = 'Malformed token';
        } else if (err.name === 'NotBeforeError') {
            message = 'Token not active yet';
        }

        return reply.code(401).send({ 
            error: 'Unauthorized',
            message: message
        });
    }
}
