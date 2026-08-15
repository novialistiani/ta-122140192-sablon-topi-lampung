<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel') . ' - Login')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/css/guest/auth-layout.css', 'resources/css/guest/login.css', 'resources/css/auth/forgot-password.css', 'resources/js/app.js', 'resources/js/guest/auth-layout.js'])

        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot }}
        @endif

        @stack('scripts')
    </body>
</html>