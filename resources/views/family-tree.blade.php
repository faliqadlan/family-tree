<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree</title>
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-slate-100 p-6">
    <div class="mx-auto max-w-3xl rounded-lg bg-white p-6 shadow">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Family Tree</h1>
                <p class="mt-1 text-slate-600">Welcome, {{ auth()->user()->name }}.</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-700">Logout</button>
            </form>
        </div>
    </div>
</body>

</html>