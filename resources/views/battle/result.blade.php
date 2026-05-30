@extends('layouts.app')

@section('title', 'Resultado — Pokemon Battle')

@section('content')

{{-- ── Page heading ── --}}
<div class="text-center mb-8 animate-fade-in">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
        <span class="text-yellow-400">Resultado</span>
        <span class="text-white"> da Batalha</span>
    </h1>
</div>

{{-- ── Result banner ── --}}
@if ($result->isDraw())
    <div class="mb-8 rounded-2xl border border-gray-700 bg-gray-900 px-6 py-6
                text-center animate-slide-up">
        <p class="text-5xl mb-2 select-none" aria-hidden="true">⚔️</p>
        <p class="text-2xl font-extrabold text-gray-200">Empate!</p>
        <p class="text-sm text-gray-600 mt-1">Ambos os Pokémon têm o mesmo HP base.</p>
    </div>
@else
    <div class="mb-8 rounded-2xl border border-yellow-500/30 bg-yellow-400/5 px-6 py-6
                text-center animate-slide-up">
        <p class="text-5xl mb-2 select-none" aria-hidden="true">🏆</p>
        <p class="text-2xl font-extrabold text-yellow-400 capitalize">
            {{ $result->getWinner()->name }} venceu!
        </p>
        <p class="text-sm text-gray-500 mt-1">
            HP: <span class="font-bold text-yellow-300">{{ $result->getWinner()->hp }}</span>
            vs
            <span class="font-bold text-gray-400">
                {{ $result->getWinner() === $result->pokemonOne
                    ? $result->pokemonTwo->hp
                    : $result->pokemonOne->hp }}
            </span>
        </p>
    </div>
@endif

{{-- ── Pokémon cards + VS ── --}}
@php
    /* PHP-driven type-badge colours — avoids flash of unstyled content */
    $typeColors = [
        'normal'          => 'bg-gray-500 text-white',
        'fire'            => 'bg-orange-500 text-white',
        'water'           => 'bg-blue-500 text-white',
        'electric'        => 'bg-yellow-400 text-gray-900',
        'grass'           => 'bg-green-500 text-white',
        'ice'             => 'bg-cyan-300 text-gray-900',
        'fighting'        => 'bg-red-700 text-white',
        'poison'          => 'bg-purple-600 text-white',
        'ground'          => 'bg-yellow-700 text-white',
        'flying'          => 'bg-indigo-400 text-white',
        'psychic'         => 'bg-pink-500 text-white',
        'bug'             => 'bg-lime-500 text-gray-900',
        'rock'            => 'bg-stone-600 text-white',
        'ghost'           => 'bg-violet-800 text-white',
        'dragon'          => 'bg-indigo-700 text-white',
        'dark'            => 'bg-neutral-800 text-gray-300',
        'steel'           => 'bg-slate-500 text-white',
        'fairy'           => 'bg-pink-300 text-gray-900',
    ];

    $statLabels = [
        'hp'               => 'HP',
        'attack'           => 'Ataque',
        'defense'          => 'Defesa',
        'special-attack'   => 'Atq. Esp.',
        'special-defense'  => 'Def. Esp.',
        'speed'            => 'Velocidade',
    ];

    $cards = [
        ['pokemon' => $result->pokemonOne, 'accent' => 'red'],
        ['pokemon' => $result->pokemonTwo, 'accent' => 'blue'],
    ];
@endphp

{{--
    Layout: flex column on mobile, flex row on lg+.
    VS circle sits between the two cards.
    `items-stretch` ensures the VS column fills the full row height on desktop.
--}}
<div class="flex flex-col lg:flex-row items-stretch gap-2 mb-10 animate-slide-up">

    @foreach ($cards as $card)
    @php
        $pokemon  = $card['pokemon'];
        $accent   = $card['accent'];
        $isWinner = $result->getWinner() !== null
                 && $result->getWinner()->name === $pokemon->name;
    @endphp

    {{-- VS separator — rendered before the second card --}}
    @if ($loop->index === 1)
    <div class="flex items-center justify-center py-2 lg:py-0 shrink-0">
        <div class="w-16 h-16 rounded-full bg-gray-950 border-2 border-gray-800 shadow-2xl
                    flex items-center justify-center">
            <span class="text-xl font-black tracking-tighter leading-none
                         bg-gradient-to-br from-red-400 to-blue-400 bg-clip-text text-transparent">
                VS
            </span>
        </div>
    </div>
    @endif

    {{-- ── Pokémon card ── --}}
    <div class="relative flex-1 rounded-2xl border bg-gray-900 p-6
                flex flex-col gap-4 shadow-xl transition-all duration-200
                {{ $isWinner
                    ? 'border-yellow-500/60 ring-2 ring-yellow-400/20 shadow-yellow-900/20'
                    : 'border-gray-800' }}">

        {{-- Winner badge --}}
        @if ($isWinner)
            <div class="absolute -top-4 left-1/2 -translate-x-1/2
                        bg-yellow-400 text-gray-900 text-xs font-extrabold
                        px-4 py-1 rounded-full shadow-lg whitespace-nowrap z-10">
                🏆 Vencedor
            </div>
        @endif

        {{-- Sprite + name --}}
        <div class="flex flex-col items-center gap-2 pt-3">
            @if ($pokemon->spriteUrl)
                <img src="{{ $pokemon->spriteUrl }}"
                     alt="Sprite de {{ $pokemon->name }}"
                     width="128" height="128"
                     class="w-32 h-32 object-contain
                            {{ $isWinner
                                ? 'drop-shadow-[0_0_16px_rgba(250,204,21,0.5)]'
                                : 'opacity-80' }}"
                     loading="lazy">
            @else
                <div class="w-32 h-32 rounded-2xl bg-gray-800 flex items-center
                             justify-center text-5xl select-none">
                    ⚔️
                </div>
            @endif

            <h2 class="text-xl font-extrabold capitalize tracking-wide
                       {{ $isWinner ? 'text-yellow-400' : 'text-white' }}">
                {{ $pokemon->name }}
            </h2>
        </div>

        {{-- Type badges — PHP-driven colours, no JS flash ── --}}
        <div class="flex flex-wrap justify-center gap-2">
            @foreach ($pokemon->types as $type)
                <span class="inline-block rounded-full px-3 py-0.5 text-xs font-bold capitalize
                             {{ $typeColors[$type] ?? 'bg-gray-700 text-gray-300' }}">
                    {{ $type }}
                </span>
            @endforeach
        </div>

        {{-- HP highlight box --}}
        <div class="rounded-xl border px-4 py-3 text-center
                    {{ $accent === 'red'
                        ? 'bg-red-950/50 border-red-900/40'
                        : 'bg-blue-950/50 border-blue-900/40' }}">
            <p class="text-xs text-gray-600 uppercase tracking-wider mb-0.5">HP Base</p>
            <p class="text-5xl font-black {{ $isWinner ? 'text-yellow-400' : 'text-white' }}">
                {{ $pokemon->hp }}
            </p>
        </div>

        {{-- Stat bars --}}
        @if (!empty($pokemon->stats))
        <div class="space-y-2.5">
            @foreach ($pokemon->stats as $statName => $statValue)
            @php
                $barColor = $statValue >= 150 ? 'bg-yellow-400'
                          : ($statValue >= 100 ? 'bg-green-500'
                          : ($statValue >= 60  ? 'bg-blue-500'
                          : 'bg-gray-500'));
            @endphp
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-500 font-medium">
                        {{ $statLabels[$statName] ?? ucfirst($statName) }}
                    </span>
                    <span class="font-bold {{ $statValue >= 100 ? 'text-yellow-400' : 'text-gray-400' }}">
                        {{ $statValue }}
                    </span>
                </div>
                <div class="h-2 rounded-full bg-gray-800 overflow-hidden">
                    <div class="h-full rounded-full stat-bar {{ $barColor }}"
                         data-value="{{ $statValue }}"
                         style="width: 0%">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
    {{-- /card --}}

    @endforeach

</div>

{{-- ── Nova Batalha — prominent red gradient button ── --}}
<div class="text-center pb-4">
    <a href="{{ route('battle.index') }}"
       class="inline-flex items-center gap-3 rounded-2xl
              bg-gradient-to-r from-red-600 to-rose-500
              hover:from-red-500 hover:to-rose-400
              text-white font-extrabold text-lg px-12 py-4
              shadow-xl shadow-red-900/30 hover:shadow-red-900/50
              transition-all duration-150 active:scale-95
              focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2
              focus:ring-offset-gray-950">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Nova Batalha
    </a>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        /* Animate stat bars from 0% → computed width */
        document.querySelectorAll('.stat-bar[data-value]').forEach(function (bar) {
            var value = parseInt(bar.dataset.value, 10);
            var pct   = Math.min(100, Math.round((value / 255) * 100));

            /* Double rAF ensures the transition fires after the initial paint at 0% */
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    bar.style.width = pct + '%';
                });
            });
        });
    });
</script>
@endsection
