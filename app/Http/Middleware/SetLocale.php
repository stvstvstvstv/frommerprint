<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['en', 'ro'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            app()->setLocale(auth()->user()->locale);
        } else {
            // Guests (login, password reset): follow the browser's language.
            // The app default goes first so it is the fallback for other languages.
            $default = config('app.locale');
            $locales = array_unique([$default, ...self::SUPPORTED_LOCALES]);
            app()->setLocale($request->getPreferredLanguage($locales) ?? $default);
        }

        return $next($request);
    }
}
