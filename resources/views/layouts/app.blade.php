<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pokemon Battle Simulator')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'bounce-slow': 'bounce 2s infinite',
                        'pulse-fast':  'pulse 1s infinite',
                        'fade-in':     'fadeIn 0.5s ease-in-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%':   { opacity: '0', transform: 'translateY(16px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                },
            },
        }
    </script>
    <style>
        /* Pokéball spinner for loading state */
        .pokeball {
            width: 24px; height: 24px;
            border-radius: 50%;
            background: linear-gradient(to bottom, #ef4444 50%, #fff 50%);
            border: 3px solid #1f2937;
            position: relative;
            animation: spin 1s linear infinite;
        }
        .pokeball::after {
            content: '';
            position: absolute;
            width: 8px; height: 8px;
            background: #fff;
            border: 3px solid #1f2937;
            border-radius: 50%;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    @yield('head')
</head>
<body class="h-full bg-gray-900 text-gray-100 antialiased">

    {{-- ── Navigation ── --}}
    <nav class="bg-gray-800 border-b border-gray-700 shadow-lg">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center gap-3">
            <span class="text-2xl select-none">&#x26BF;</span>
            <a href="{{ route('battle.index') }}"
               class="text-xl font-bold tracking-tight text-white hover:text-yellow-400 transition-colors">
                Pokemon Battle
            </a>
            <span class="ml-auto text-xs text-gray-500">Powered by PokéAPI</span>
        </div>
    </nav>

    {{-- ── Main content ── --}}
    <main class="max-w-5xl mx-auto px-4 py-10">
        @yield('content')
    </main>

    {{-- ── Footer ── --}}
    <footer class="mt-16 border-t border-gray-800 py-6 text-center text-xs text-gray-600">
        Pokemon Battle Simulator &mdash; ateliware challenge
    </footer>

</body>
</html>
