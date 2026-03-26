<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:setup-superadmin', function () {
    $name = env('SUPERADMIN_NAME', 'Super Admin');
    $email = env('SUPERADMIN_EMAIL');
    $password = env('SUPERADMIN_PASSWORD');

    if (blank($email) || blank($password)) {
        $this->error('Set SUPERADMIN_EMAIL and SUPERADMIN_PASSWORD in .env first.');

        return 1;
    }

    $user = User::query()->firstOrNew(['email' => $email]);

    $user->name = $name;
    $user->password = Hash::make($password);
    $user->role = 'admin';

    if (! array_key_exists('email_verified_at', $user->getAttributes()) || is_null($user->email_verified_at)) {
        $user->email_verified_at = now();
    }

    $user->save();

    $this->info("Superadmin ready: {$email}");

    return 0;
})->purpose('Create or update superadmin from SUPERADMIN_* env values');
