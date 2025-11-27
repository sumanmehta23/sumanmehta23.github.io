<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($this->is419Error($exception)) {
            return $this->handle419Error($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Check if the exception is a 419 CSRF token mismatch error.
     */
    protected function is419Error(Throwable $exception): bool
    {
        return $exception instanceof TokenMismatchException
            || ($exception instanceof HttpException && $exception->getStatusCode() === 419);
    }

    /**
     * Handle 419 CSRF token mismatch errors.
     */
    protected function handle419Error(Request $request, Throwable $exception)
    {
        // Log error
        $user = Auth::user();
        Log::warning('419 CSRF Token Mismatch', [
            'user_id' => $user ? $user->id : null,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Your session has expired. Please refresh the page and try again.',
            ], 419);
        }

        $user = Auth::user();
        $path = $request->path();
        $isAdmin = str_starts_with($path, 'admin');
        $isAuthRoute = str_contains($path, 'login') || str_contains($path, 'register');
        $referer = $this->getValidReferer($request->header('referer'));

        // Determine redirect URL
        if ($isAuthRoute || !$user) {
            $redirectUrl = $isAdmin ? route('admin.login') : route('login');
            $message = 'Your session has expired. Please login again.';
            
            if ($referer && !$isAuthRoute) {
                session()->put('url.intended', $referer);
            }
        } else {
            $redirectUrl = $referer ?: ($isAdmin ? '/admin' : RouteServiceProvider::HOME);
            $message = 'Your session has expired. Please try again.';
        }

        // Prepare redirect with form data
        $redirect = redirect($redirectUrl)->with('error', $message);
        
        if ($formData = $this->prepareFormData($request)) {
            $redirect->withInput($formData);
        }

        return $redirect;
    }

    /**
     * Get valid referer URL if from same domain.
     */
    protected function getValidReferer(?string $referer): ?string
    {
        if (!$referer) {
            return null;
        }

        try {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            
            return $refererHost === $appHost ? $referer : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Prepare form data for flashing, excluding passwords.
     */
    protected function prepareFormData(Request $request): array
    {
        $data = $request->except($this->dontFlash);
        
        // Remove password fields
        foreach (['old_password', 'new_password'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}
