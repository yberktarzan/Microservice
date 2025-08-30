<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locale middleware for Auth microservice.
 *
 * Sets application locale based on Accept-Language header.
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getLocaleFromHeader($request);

        if ($locale && $this->isValidLocale($locale)) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Get locale from Accept-Language header.
     */
    private function getLocaleFromHeader(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (! $acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header (e.g., "en-US,en;q=0.9,tr;q=0.8")
        $languages = explode(',', $acceptLanguage);

        foreach ($languages as $language) {
            $locale = trim(explode(';', $language)[0]);
            $locale = strtolower(substr($locale, 0, 2)); // Get first 2 characters

            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Check if locale is valid and supported.
     */
    private function isValidLocale(string $locale): bool
    {
        $supportedLocales = ['en', 'tr'];

        return in_array($locale, $supportedLocales, true);
    }
}
