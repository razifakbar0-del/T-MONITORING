<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/alpinejs" defer></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-cover bg-center bg-no-repeat bg-fixed" style="background-image: url('https://www.eigeradventure.com/blog/wp-content/uploads/2024/03/keindahan-alam-di-indonesia-1.jpg');">
            @include('layouts.navigation')

            @if (isset($header))
                <header class="bg-white/70 shadow backdrop-blur-sm"> <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>