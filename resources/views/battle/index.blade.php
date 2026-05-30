@extends('layouts.app')

@section('title', 'Pokemon Battle Simulator')

@section('content')

{{-- ── Hero ── --}}
<div class="text-center mb-12 animate-fade-in">
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl
                bg-gray-900 border border-gray-800 shadow-2xl mb-5 text-4xl select-none"
         aria-hidden="true">⚔️</div>
    <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-3">
        <span class="text-yellow-400">Pokemon</span>
        <span class="text-white"> Battle</span>
    </h1>
    <p class="text-gray-500 text-sm max-w-xs mx-auto leading-relaxed">
        Compare o HP base de dois Pokémon e descubra quem vence a batalha!
    </p>
</div>

{{-- ── Error alert (battle errors) ── --}}
@if ($errors->has('battle'))
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-900/60
                bg-red-950/70 px-5 py-4 text-red-300 animate-slide-up"
         role="alert">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <ul class="space-y-1 text-sm">
            @foreach ($errors->get('battle') as $battleError)
                <li class="font-medium">{{ $battleError }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── Validation errors ── --}}
@if ($errors->any() && !$errors->has('battle'))
    <div class="mb-6 rounded-2xl border border-red-900/60 bg-red-950/70 px-5 py-4
                text-red-300 animate-slide-up"
         role="alert">
        <ul class="list-disc list-inside space-y-1 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── Battle form ── --}}
<form method="POST" action="{{ route('battle.fight') }}"
      id="battle-form"
      class="bg-gray-900 rounded-3xl border border-gray-800 shadow-2xl p-6 sm:p-8 mb-10 animate-slide-up">
    @csrf

    {{-- Two-column on sm+, one-column on mobile --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        {{-- Pokémon 1 --}}
        <div>
            <label for="pokemon_one" class="block text-sm font-semibold text-gray-300 mb-2">
                <span class="inline-block w-2 h-2 rounded-full bg-red-500 mr-2 align-middle"></span>Pokémon 1
            </label>
            <input
                type="text"
                id="pokemon_one"
                name="pokemon_one"
                value="{{ old('pokemon_one') }}"
                placeholder="ex: pikachu"
                autocomplete="off"
                spellcheck="false"
                class="w-full rounded-xl bg-gray-800 border border-gray-700 text-white
                       placeholder-gray-600 px-4 py-3 text-sm
                       focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent
                       transition duration-150
                       @error('pokemon_one') border-red-600 ring-1 ring-red-600 @enderror"
            >
            @error('pokemon_one')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Pokémon 2 --}}
        <div>
            <label for="pokemon_two" class="block text-sm font-semibold text-gray-300 mb-2">
                <span class="inline-block w-2 h-2 rounded-full bg-blue-500 mr-2 align-middle"></span>Pokémon 2
            </label>
            <input
                type="text"
                id="pokemon_two"
                name="pokemon_two"
                value="{{ old('pokemon_two') }}"
                placeholder="ex: raichu"
                autocomplete="off"
                spellcheck="false"
                class="w-full rounded-xl bg-gray-800 border border-gray-700 text-white
                       placeholder-gray-600 px-4 py-3 text-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                       transition duration-150
                       @error('pokemon_two') border-red-600 ring-1 ring-red-600 @enderror"
            >
            @error('pokemon_two')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- VS divider --}}
    <div class="flex items-center gap-4 my-7">
        <div class="flex-1 h-px bg-gradient-to-r from-transparent to-red-900/40"></div>
        <div class="w-9 h-9 rounded-full bg-gray-800 border border-gray-700
                    flex items-center justify-center shrink-0 shadow-inner">
            <span class="text-xs font-black text-gray-500 tracking-tight">VS</span>
        </div>
        <div class="flex-1 h-px bg-gradient-to-l from-transparent to-blue-900/40"></div>
    </div>

    {{-- Submit button — red gradient, hover effect, loading state --}}
    <div class="text-center">
        <button
            type="submit"
            id="submit-btn"
            class="inline-flex items-center justify-center gap-2.5 rounded-2xl
                   bg-gradient-to-r from-red-600 to-rose-500
                   hover:from-red-500 hover:to-rose-400
                   text-white font-extrabold text-base px-12 py-3.5
                   shadow-lg shadow-red-900/40 hover:shadow-red-900/60
                   transition-all duration-150 active:scale-95
                   disabled:opacity-60 disabled:cursor-not-allowed disabled:active:scale-100
                   focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2
                   focus:ring-offset-gray-900"
        >
            <span id="btn-icon" aria-hidden="true">⚔️</span>
            <span id="btn-text">Batalhar!</span>
            <span id="btn-loading" class="hidden" aria-hidden="true">
                <span class="pokeball"></span>
            </span>
        </button>
    </div>

</form>

{{-- ── Recent battles — mini-cards ── --}}
@if ($recentBattles->isNotEmpty())
<section>
    <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest mb-4 flex items-center gap-2">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Últimas batalhas
    </h2>

    {{-- Two-column grid on sm+, one-column on mobile --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach ($recentBattles as $battle)
        <article class="bg-gray-900 rounded-2xl border border-gray-800 p-4 shadow-lg
                        hover:border-gray-700 transition-colors duration-150">

            {{-- Pokemon names + HPs --}}
            <div class="flex items-center gap-3 mb-3">

                {{-- Pokémon 1 --}}
                <div class="flex-1 text-center min-w-0">
                    <p class="text-xs font-bold capitalize truncate mb-1
                              {{ $battle->result === 'pokemon_one_wins' ? 'text-yellow-400' : 'text-gray-500' }}">
                        @if ($battle->result === 'pokemon_one_wins')🏆 @endif{{ $battle->pokemon_one_name }}
                    </p>
                    <p class="text-3xl font-black leading-none
                              {{ $battle->result === 'pokemon_one_wins' ? 'text-yellow-400' : 'text-gray-300' }}">
                        {{ $battle->pokemon_one_hp }}
                    </p>
                    <p class="text-[10px] text-gray-700 uppercase tracking-widest mt-1">HP</p>
                </div>

                {{-- VS bubble --}}
                <div class="w-8 h-8 rounded-full bg-gray-800 border border-gray-700
                             flex items-center justify-center shrink-0">
                    <span class="text-[10px] font-black text-gray-600 leading-none">VS</span>
                </div>

                {{-- Pokémon 2 --}}
                <div class="flex-1 text-center min-w-0">
                    <p class="text-xs font-bold capitalize truncate mb-1
                              {{ $battle->result === 'pokemon_two_wins' ? 'text-yellow-400' : 'text-gray-500' }}">
                        @if ($battle->result === 'pokemon_two_wins')🏆 @endif{{ $battle->pokemon_two_name }}
                    </p>
                    <p class="text-3xl font-black leading-none
                              {{ $battle->result === 'pokemon_two_wins' ? 'text-yellow-400' : 'text-gray-300' }}">
                        {{ $battle->pokemon_two_hp }}
                    </p>
                    <p class="text-[10px] text-gray-700 uppercase tracking-widest mt-1">HP</p>
                </div>

            </div>

            {{-- Divider --}}
            <div class="h-px bg-gray-800 mb-3"></div>

            {{-- Result badge + timestamp --}}
            <div class="flex items-center justify-between text-xs">
                @if ($battle->result === 'draw')
                    <span class="text-gray-600 flex items-center gap-1.5">
                        ⚔️ <span>Empate</span>
                    </span>
                @else
                    <span class="text-yellow-500 font-semibold flex items-center gap-1.5">
                        🏆 <span class="capitalize">{{ $battle->winner_name }} venceu</span>
                    </span>
                @endif
                <span class="text-gray-700">{{ $battle->created_at->diffForHumans() }}</span>
            </div>

        </article>
        @endforeach
    </div>
</section>
@else
<div class="text-center py-16 text-gray-700">
    <p class="text-5xl mb-4 select-none" aria-hidden="true">⚔️</p>
    <p class="text-sm">Nenhuma batalha ainda. Seja o primeiro a lutar!</p>
</div>
@endif

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form    = document.getElementById('battle-form');
        var btn     = document.getElementById('submit-btn');
        var icon    = document.getElementById('btn-icon');
        var text    = document.getElementById('btn-text');
        var loading = document.getElementById('btn-loading');

        form.addEventListener('submit', function () {
            btn.disabled = true;
            icon.classList.add('hidden');
            text.textContent = 'Batalhando\u2026';
            loading.classList.remove('hidden');
        });
    });
</script>
@endsection
