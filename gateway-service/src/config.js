import dotenv from 'dotenv';
dotenv.config({ path: process.cwd() + '/../.env' });

/**
 * @fileoverview Centralized configuration for the Gateway service.
 * 
 * This file loads environment variables (via dotenv) and exposes them
 * as a strongly-typed configuration object.
 * 
 */

/**
 * Global configuration object for the Gateway.
 *
 * @constant
 * @type {Object}
 * @property {number} port - Port where the Gateway will run.
 *   - Source: `process.env.PORT`
 *   - Default: `8080`
 *
 * @property {string} jwtSecret - Secret key for signing/verifying JWT tokens.
 *   - Source: `process.env.JWT_SECRET`
 *   - Default: `"change_me"`
 *
 * @property {Object} services - Upstream microservice base URLs.
 * @property {string} services.auth - Auth service base URL.
 *   - Source: `process.env.AUTH_BASE_URL`
 *   - Default: `"http://localhost:8000"`
 * @property {string} services.admin - Admin service base URL.
 *   - Source: `process.env.ADMIN_BASE_URL`
 *   - Default: `"http://localhost:8001"`
 *
 * @property {Object} security - Security-related configurations.
 * @property {Object} security.rateLimit - Rate limiting settings.
 * @property {number} security.rateLimit.max - Max requests allowed per client in a time window.
 *   - Source: `process.env.RATE_LIMIT_MAX`
 *   - Default: `200`
 * @property {string} security.rateLimit.timeWindow - Time window duration for rate limit.
 *   - Source: `process.env.RATE_LIMIT_WINDOW`
 *   - Default: `"1 minute"`
 */
export const config = {
    port: parseInt(process.env.PORT || '8080', 10),
    nodeEnv: process.env.NODE_ENV || 'development',
    
    // Laravel Auth Service JWT Configuration
    auth: {
        // JWT secret key - should match Laravel's JWT_SECRET
        jwtSecret: process.env.JWT_SECRET || 'change_me',
        // Laravel Auth Service URL for token validation (fallback)
        validateUrl: process.env.AUTH_VALIDATE_URL || 'http://localhost:8000/api/auth/validate',
        // JWT issuer - should match Laravel's JWT issuer
        jwtIssuer: process.env.JWT_ISSUER || 'laravel-auth-service',
        // JWT audience - should match Laravel's JWT audience  
        jwtAudience: process.env.JWT_AUDIENCE || 'microservices',
    },
    
    services: {
        auth: process.env.AUTH_BASE_URL || 'http://localhost:8000',
        admin: process.env.ADMIN_BASE_URL || 'http://localhost:8001',
        example: process.env.EXAMPLE_BASE_URL || 'http://localhost:9000',
    },
    
    security: {
        rateLimit: {
            max: parseInt(process.env.RATE_LIMIT_MAX || '200', 10),
            timeWindow: process.env.RATE_LIMIT_WINDOW || '1 minute',
        },
        cors: {
            origin: process.env.CORS_ORIGIN || 'http://localhost:3000',
            credentials: true,
        },
    },
    
    logging: {
        level: process.env.LOG_LEVEL || 'info',
        prettyPrint: process.env.NODE_ENV !== 'production',
    },
};
