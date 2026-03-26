<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    if (! $user instanceof User) {
        Auth::logout();

        return redirect()->route('login');
    }

    return $user->isSuperAdmin()
        ? redirect()->to('/admin')
        : redirect()->route('family-tree');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user instanceof User) {
            Auth::logout();

            return redirect()->route('login');
        }

        return redirect()->intended(
            $user->isSuperAdmin() ? '/admin' : route('family-tree')
        );
    })->name('login.attempt');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::get('/family-tree', function () {
    $user = Auth::user();

    if (! $user instanceof User) {
        Auth::logout();

        return redirect()->route('login');
    }

    if ($user->isSuperAdmin()) {
        return redirect()->to('/admin');
    }

    return view('family-tree');
})->middleware('auth')->name('family-tree');
