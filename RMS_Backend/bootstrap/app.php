<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Intercept database errors - never expose raw SQL to the client
        $exceptions->render(function (QueryException $e, Request $request) {
            if (!($request->is('api/*') || $request->expectsJson())) {
                return null; // Let the default handler deal with web requests
            }

            $code = $e->getCode();

            // Map common MySQL/MariaDB error codes to friendly messages
            $friendlyMessages = [
                '22003' => 'One of the numeric values you entered is too large for the field. Please enter a smaller number.',
                '1264' => 'One of the numeric values you entered is too large for the field. Please enter a smaller number.',
                '1062' => 'This record already exists. Please check for duplicates.',
                '1452' => 'The selected option is invalid or has been removed. Please refresh and try again.',
                '1048' => 'A required field is missing a value. Please fill in all required fields.',
                '1406' => 'One of the values you entered is too long. Please shorten it and try again.',
                '1366' => 'One of the fields received an invalid value type. Please check your inputs.',
            ];

            $message = $friendlyMessages[(string)$code]
                ?? 'A database error occurred. Please check your input values and try again.';

            return response()->json([
                'message' => $message,
            ], 422);
        });
    })->create();

