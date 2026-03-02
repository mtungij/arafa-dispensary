<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Configure the application
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add global middleware here if needed
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Render callback for all exceptions
        $exceptions->render(function (\Throwable $e, Request $request) {

            // 1️⃣ Handle Livewire AJAX requests
            if ($request->header('X-Livewire')) {

                // Log the exception with component info
                Log::error('Livewire Exception', [
                    'component' => $request->header('X-Livewire-Component', 'unknown'),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Return clean JSON for Livewire frontend
                return response()->json([
                    'error' => $e->getMessage(),
                    'type' => get_class($e),
                ], 500);
            }

            // 2️⃣ For normal web requests, fall back to default Laravel renderer
            return null;
        });

        // Optional: customize reporting for specific exceptions
        $exceptions->report(function (\Throwable $e) {
            // Example: ignore 404 errors in logs
            if ($e instanceof Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return false; // do not log
            }

            // Log everything else normally
            return true;
        });
    })
    ->create();