<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::get('/sanctum/csrf-cookie', fn () => response()->noContent());

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (! Auth::attempt($credentials, true)) {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }
        return back()->withErrors([
            'email' => __('auth.failed'),
        ]);
    }

    $request->session()->regenerate();

    if ($request->expectsJson()) {
        return response()->json(['message' => 'OK']);
    }

    return redirect()->intended(route('dashboard', absolute: false));
});

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    if ($request->expectsJson()) {
        return response()->json(['message' => 'OK']);
    }

    return redirect('/');
});

