<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LGI Store') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/css/auth/auth-layout.css'])
</head>
<body>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 px-6">
        <div class="logo">
            <a href="/">
                <img src="{{ asset('images/logo.png') }}" alt="LGI Store Logo">
            </a>
        </div>

        <div class="auth-card w-full">
            {{ $slot }}
        </div>
    </div>
</body>
</html>