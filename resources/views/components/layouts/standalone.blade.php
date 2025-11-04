<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'System Sync' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Vite and Livewire assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100 dark:bg-gray-900 flex items-center justify-center">

{{-- Main container --}}
<main class="w-full max-w-lg mx-auto text-center p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-lg">
    {{ $slot }}
</main>

@livewireScripts
</body>
</html>
