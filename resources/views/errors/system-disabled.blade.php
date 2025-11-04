<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Disabled</title>

    {{-- Tailwind CSS --}}
    @vite(['resources/css/app.css'])
</head>

<body class="h-full flex items-center justify-center bg-[#0b0f19] text-gray-100 font-sans antialiased">
<div class="relative max-w-lg w-full mx-4 p-10 rounded-3xl bg-[#1a2234] shadow-2xl border border-gray-700/50 transition-all duration-300 hover:scale-[1.01]">

    {{-- Top warning symbol --}}
    <div class="absolute -top-12 left-1/2 transform -translate-x-1/2">
        <div class="flex items-center justify-center h-24 w-24 rounded-full bg-red-600 shadow-lg shadow-red-900/50 ring-8 ring-[#0b0f19]">
            {{-- ⚠️ Warning symbol via SVG --}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="w-12 h-12">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z" />
            </svg>
        </div>
    </div>

    {{-- Title & Message --}}
    <div class="mt-16">
        <h1 class="text-3xl font-bold mb-4 tracking-tight text-white">
            System Disabled
        </h1>
        <p class="text-gray-400 leading-relaxed mb-8">
            {{ $message ?? 'This system has been disabled or is not yet registered. Please contact your system administrator or the central office for assistance.' }}
        </p>

        {{-- Optional Site Code --}}
        <p class="text-sm text-gray-500 mb-8">
                <span class="inline-block bg-gray-800/60 text-gray-300 font-mono px-3 py-1 rounded-md">
                    Site Code: {{ config('app.site_code') }}
                </span>
        </p>

        {{-- Action button --}}
        <div class="flex justify-center">
            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-100 font-medium rounded-lg transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Return to Dashboard
            </a>
        </div>
    </div>

    {{-- Footer --}}
    <div class="mt-12 text-xs text-gray-500 border-t border-gray-700/70 pt-4">
        &copy; {{ date('Y') }} Cashiering System v2
        <span class="text-gray-600">•</span>
        Internal Access Only
    </div>
</div>
</body>
</html>
