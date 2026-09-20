<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * SESSION_LIFETIME bawaan Laravel adalah batas MENGANGGUR: setiap request
 * memperbaruinya, sehingga sesi yang dipakai terus-menerus tidak pernah
 * berakhir. Middleware ini menambahkan batas MUTLAK sejak waktu login,
 * terlepas dari seberapa aktif penggunanya.
 */
class EnforceAbsoluteSessionLifetime
{
    public const KEY = 'auth.login_at';

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $minutes = (int) config('session.absolute_lifetime', 120);

        if ($minutes <= 0) {
            return $next($request);
        }

        $loginAt = $request->session()->get(self::KEY);

        // Sesi yang sudah berjalan sebelum fitur ini ada belum punya penanda;
        // beri penanda sekarang agar tidak langsung terputus.
        if (! is_int($loginAt)) {
            $request->session()->put(self::KEY, now()->getTimestamp());

            return $next($request);
        }

        if (now()->getTimestamp() - $loginAt < $minutes * 60) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'Sesi berakhir setelah '.$minutes.' menit. Silakan masuk kembali.';

        if ($request->expectsJson() || $request->header('X-Livewire')) {
            abort(419, $message);
        }

        return redirect()->route('dashboard.login')->with('session-expired', $message);
    }
}
