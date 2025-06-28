<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request; // <-- Pastikan baris ini ada

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Cek apakah request ini mengharapkan balasan JSON (ini adalah ciri khas request API)
        if ($request->expectsJson()) {
            // Jika ya, jangan redirect. Kembalikan null.
            // Laravel akan secara otomatis mengubah ini menjadi response 401 Unauthenticated.
            return null;
        }

        // Jika tidak (misalnya ini aplikasi web biasa), baru redirect ke route bernama 'login'.
        return route('login');
    }
}