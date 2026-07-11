<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Soil Classifier</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }

        .glass {
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.85);
        }

        .input-modern {
            transition: 0.3s;
        }

        .input-modern:focus {
            box-shadow: 0 0 0 2px #22c55e;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-500 to-green-700">

<div class="w-full max-w-5xl grid md:grid-cols-2 bg-white rounded-2xl shadow-xl overflow-hidden">

    <!-- LEFT SIDE -->
    <div class="hidden md:flex flex-col justify-center items-center bg-gradient-to-br from-green-600 to-green-800 text-white p-10">

        <h1 class="text-3xl font-bold mb-4">🌱 Soil Classifier</h1>
        <p class="text-center text-sm opacity-90">
            Monitoring kesuburan tanah secara realtime menggunakan IoT & AI
        </p>

        <div class="mt-6 text-6xl">
            🌾
        </div>

    </div>

    <!-- RIGHT SIDE (FORM) -->
    <div class="p-8 flex flex-col justify-center">

        {{ $slot }}

    </div>

</div>

</body>
</html>
