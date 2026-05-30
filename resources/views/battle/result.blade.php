@extends('layouts.app')

@section('title', 'Resultado da Batalha')

@section('head')
<script>
    /* Type badge colours — maps PokeAPI type name → Tailwind bg/text classes */
    const TYPE_COLOURS = {
        normal:   'bg-gray-500 text-white',
        fire:     'bg-orange-500 text-white',
        water:    'bg-blue-500 text-white',
        electric: 'bg-yellow-400 text-gray-900',
        grass:    'bg-green-500 text-white',
        ice:      'bg-cyan-400 text-gray-900',
        fighting: 'bg-red-700 text-white',
        poison:   'bg-purple-600 text-white',
        ground:   'bg-yellow-700 text-white',
        flying:   'bg-indigo-400 text-white',
        psychic:  'bg-pink-500 text-white',
        bug:      'bg-lime-500 text-gray-900',
        rock:     'bg-yellow-800 text-white',
        ghost:    'bg-violet-800 text-white',
        dragon:   'bg-indigo-700 text-white',
        dark:     'bg-gray-800 text-gray-300',
        steel:    'bg-slate-400 text-white',
        fairy:    'bg-pink-300 text-gray-900',
    };

    /* Stat display labels */
    const STAT_LABELS = {
        hp:              'HP',
        attack:          'Ataque',
        defense:         'Defesa',
        'special-attack': 'Atq. Esp.',
        'special-defense':'Def. Esp.',
        speed:           'Velocidade',
    };

    document.addEventListener('DOMContentLoaded', () => {
        /* Render type badges */
        document.querySelectorAll('[data-type-badge]').forEach(el => {
            const type = el.dataset.typeBadge;
            const cls  = TYPE_COLOURS[type] ?? 'bg-gray-600 text-white';
            el.className = `inline-block rounded-full px-3 py-0.5 text-xs font-bold capitalize ${cls}`;
        });

        /* Render stat bars with animation */
        document.querySelectorAll('[data-stat-bar]').forEach(bar => {
            const value = parseInt(bar.dataset.statBar, 10);
            const pct   = Math.round((value / 255) * 100);
            bar.style.width = '0%';
            bar.style.transition = 'width 0.8s ease-out';
            setTimeout(() => { bar.style.width = pct + '%'; }, 100);
        });
    });
</script>
@endsection

@section('content')

{{-- ── Page title ── --}}
<div class="text-center mb-10 animate-fade-in">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
        <span class="text-yellow-400">Resultado</span>
        <span class="text-white"> da Batalha</span>
    </h1>
</div>

{{-- ── Result banner ── --}}
@if ($result->isDraw())
    <div class="mb-8 rounded-2xl border border-gray-600 bg-gray-800 px-6 py-5 text-center animate-fade-in">
        <p class="text-4xl mb-2 select-none">&#x1F91D;</p>
        <p class="text-2xl font-extrabold text-gray-300">Empate!</p>
        <p class="text-sm text-gray-500 mt-1">Ambos têm o mesmo HP base.</p>
    </div>
@else
    <div class="mb-8 rounded-2xl border border-yellow-500/40 bg-yellow-400/10 px-6 py-5 text-center animate-fade-in">
        <p class="text-4xl mb-2 select-none">&#x1F3C6;</p>
        <p class="text-2xl font-extrabold text-yellow-400 capitalize">
            {{ $result->getWinner()->name }} venceu!
        </p>
        <p class="text-sm text-gray-400 mt-1">
            HP: <span class="font-bold text-yellow-300">{{ $result->getWinner()->hp }}</span>
            vs {{ $result->isDraw() ? '—' : ($result->getWinner() === $result->pokemonOne ? $result->pokemonTwo->hp : $result->pokemonOne->hp) }}
        </p>
    </div>
@endif

{{-- ── Pokemon cards ── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">

    {{-- Card macro: pokemonOne --}}
    @php
        $cards = [
            ['pokemon' => $result->pokemonOne,  'side' => 'one', 'accent' => 'red'],
            ['pokemon' => $result->pokemonTwo,  'side' => 'two', 'accent' => 'blue'],
        ];
    @endphp

    @foreach ($cards as ['pokemon' => $pokemon, 'side' => $side, 'accent' => $accent])
    @php
        $isWinner = $result->getWinner() && $result->getWinner()->name === $pokemon->name;
        $ringClass = $isWinner
            ? 'border-yellow-500/70 ring-2 ring-yellow-400/30'
            : 'border-gray-700';
    @endphp
    <div class="relative rounded-2xl border bg-gray-800 shadow-xl p-6 flex flex-col gap-4 transition-all {{ $ringClass }}">

        {{-- Winner crown --}}
        @if ($isWinner)
            <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-yellow-400 text-gray-900
                        text-xs font-bold px-3 py-0.5 rounded-full shadow">
                &#x1F451; Vencedor
            </div>
        @endif

        {{-- Sprite + name --}}
        <div class="flex flex-col items-center gap-2 pt-2">
            @if ($pokemon->spriteUrl)
                <img src="{{ $pokemon->spriteUrl }}"
                     alt="{{ $pokemon->name }}"
                     class="w-28 h-28 object-contain {{ $isWinner ? 'drop-shadow-[0_0_12px_rgba(250,204,21,0.6)]' : 'opacity-90' }}"
                     loading="lazy">
            @else
                <div class="w-28 h-28 rounded-full bg-gray-700 flex items-center justify-center text-4xl select-none">
                    &#x26BF;
                </div>
            @endif
            <h2 class="text-xl font-extrabold capitalize tracking-wide
                {{ $isWinner ? 'text-yellow-400' : 'text-white' }}">
                {{ $pokemon->name }}
            </h2>
        </div>

        {{-- Type badges --}}
        <div class="flex flex-wrap justify-center gap-2">
            @foreach ($pokemon->types as $type)
                <span data-type-badge="{{ $type }}">{{ $type }}</span>
            @endforeach
        </div>

        {{-- HP highlight --}}
        <div class="rounded-xl {{ $accent === 'red' ? 'bg-red-950/60 border-red-800/40' : 'bg-blue-950/60 border-blue-800/40' }}
                    border px-4 py-3 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">HP Base</p>
            <p class="text-4xl font-black {{ $isWinner ? 'text-yellow-400' : 'text-white' }}">
                {{ $pokemon->hp }}
            </p>
        </div>

        {{-- Stats bars --}}
        @if (!empty($pokemon->stats))
        <div class="space-y-2.5">
            @foreach ($pokemon->stats as $statName => $statValue)
            <div>
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span class="font-medium">
                        {{ ['hp'=>'HP','attack'=>'Ataque','defense'=>'Defesa',
                            'special-attack'=>'Atq. Esp.','special-defense'=>'Def. Esp.',
                            'speed'=>'Velocidade'][$statName] ?? ucfirst($statName) }}
                    </span>
                    <span class="font-bold {{ $statValue >= 100 ? 'text-yellow-400' : 'text-gray-300' }}">
                        {{ $statValue }}
                    </span>
                </div>
                <div class="h-2 rounded-full bg-gray-700 overflow-hidden">
                    <div data-stat-bar="{{ $statValue }}"
                         class="h-full rounded-full
                         @if ($statValue >= 150) bg-yellow-400
                         @elseif ($statValue >= 100) bg-green-500
                         @elseif ($statValue >= 60)  bg-blue-500
                         @else                       bg-gray-500
                         @endif">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
    @endforeach

</div>

{{-- ── VS separator visible on mobile (between cards) ── --}}
{{-- (handled by grid gap on desktop) --}}

{{-- ── New battle button ── --}}
<div class="text-center">
    <a href="{{ route('battle.index') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-gray-700 hover:bg-gray-600
              text-white font-bold text-sm px-8 py-3 border border-gray-600
              shadow-lg transition-all duration-150 active:scale-95">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Nova Batalha
    </a>
</div>

@endsection
