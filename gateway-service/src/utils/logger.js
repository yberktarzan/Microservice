import pino from 'pino';
import { config } from '../config.js';

/**
 * @fileoverview Logger utility wrapping Pino with environment-based configuration.
 * Provides consistent logging across the service with proper production setup.
 */

const loggerConfig = {
    level: config.logging.level,
    ...(config.nodeEnv !== 'production' && {
        transport: {
            target: 'pino-pretty',
            options: {
                colorize: true,
                translateTime: 'HH:MM:ss',
                ignore: 'pid,hostname',
            },
        },
    }),
};

export const logger = pino(loggerConfig);
