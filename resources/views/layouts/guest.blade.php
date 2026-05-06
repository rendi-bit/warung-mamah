<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Toko Tika') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-body-simple">
    <main class="auth-simple-page">
        <a href="{{ route('home') }}" class="auth-simple-brand">
            <span class="auth-simple-logo auth-simple-logo-img">
                <img src="{{ asset('storage/avatars/mamah.jpeg') }}" alt="Logo Toko Tika">
            </span>

            <div>
                <strong>TOKO TIKA</strong>
                <small>UMKM Commerce Platform</small>
            </div>
        </a>

        <section class="auth-simple-card">
            {{ $slot }}
        </section>
    </main>
</body>
</html>