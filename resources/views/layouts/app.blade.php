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
                        'fade-in':  'fadeIn 0.4s ease-out both',
                        'slide-up': 'slideUp 0.45s ease-out both',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%':   { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%':   { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                },
            },
        }
    </script>
    <style>
        /* ── Pokéball spinner ───────────────────────────────────── */
        .pokeball {
            display: inline-block;
            width: 20px; height: 20px;
            border-radius: 50%;
            background: linear-gradient(to bottom, #ef4444 50%, #f9fafb 50%);
            border: 3px solid #030712;
            position: relative;
            animation: pokeball-spin 0.7s linear infinite;
            flex-shrink: 0;
        }
        .pokeball::after {
            content: '';
            position: absolute;
            width: 6px; height: 6px;
            background: #f9fafb;
            border: 2px solid #030712;
            border-radius: 50%;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }
        @keyframes pokeball-spin { to { transform: rotate(360deg); } }

        /* ── Stat bar smooth animation ──────────────────────────── */
        .stat-bar {
            transition: width 0.9s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
    @yield('head')
</head>
<body class="min-h-full bg-gray-950 text-gray-100 antialiased">

    {{-- ── Navigation ── --}}
    <header class="bg-gray-900 border-b border-gray-800 shadow-lg sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-3">
            <span class="text-2xl leading-none select-none" aria-hidden="true">⚔️</span>
            <a href="{{ route('battle.index') }}"
               class="font-extrabold tracking-tight text-lg hover:opacity-80 transition-opacity">
                <span class="text-yellow-400">Pokemon</span><span class="text-white"> Battle</span>
            </a>
            <span class="ml-auto text-xs text-gray-700 hidden sm:block">Powered by PokéAPI</span>
        </div>
    </header>

    {{-- ── Main content ── --}}
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-10 min-w-0">
        @yield('content')
    </main>

    {{-- ── Footer ── --}}
    <footer class="border-t border-gray-800/60 py-6 text-center text-xs text-gray-700">
        Pokemon Battle Simulator &mdash; ateliware challenge
    </footer>

    @yield('scripts')

</body>
</html>
