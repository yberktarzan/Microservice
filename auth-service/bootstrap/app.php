<?php

use App\Exceptions\Handler;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Force JSON for all requests globally (API-only service)
        $middleware->append(ForceJsonResponse::class);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => EnsureEmailIsVerified::class,
            'locale' => SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Throwable $e) {
            if ($e instanceof ValidationException) {
                return false;
            }

            $logData = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
                'request' => [
                    'method' => request()->method(),
                    'url' => request()->fullUrl(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            ];

            if (config('app.debug')) {
                $logData['full_path'] = $e->getFile();
            }

            Log::error('Exception occurred', $logData);

            return false;
        });

        $exceptions->renderable(function (\Throwable $e, $request) {
            $handler = new Handler(app());

            return $handler->render($request, $e);
        });
    })->create();
