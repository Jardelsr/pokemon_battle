@extends('layouts.app')

@section('title', 'Pokemon Battle Simulator')

@section('content')

{{-- ── Hero ── --}}
<div class="text-center mb-10 animate-fade-in">
    <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-2">
        <span class="text-yellow-400">Pokemon</span>
        <span class="text-white"> Battle</span>
    </h1>
    <p class="text-gray-400 text-sm">Digite os nomes dos Pokémon e descubra quem tem o maior HP!</p>
</div>

{{-- ── Error alert ── --}}
@if ($errors->has('battle'))
    <div class="mb-6 flex items-start gap-3 rounded-lg border border-red-700 bg-red-950 px-5 py-4 text-red-300 animate-fade-in"
         role="alert">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <ul class="space-y-1">
            @foreach ($errors->get('battle') as $battleError)
                <li class="font-medium">{{ $battleError }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── Validation errors ── --}}
@if ($errors->any() && !$errors->has('battle'))
    <div class="mb-6 rounded-lg border border-red-700 bg-red-950 px-5 py-4 text-red-300 animate-fade-in">
        <ul class="list-disc list-inside space-y-1 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── Battle form ── --}}
<form method="POST" action="{{ route('battle.fight') }}"
      class="bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl p-6 sm:p-8 mb-10"
      id="battle-form">
    @csrf

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">

        {{-- Pokemon 1 --}}
        <div>
            <label for="pokemon_one" class="block text-sm font-semibold text-gray-300 mb-2">
                <span class="text-red-400 mr-1">&#x25CF;</span> Pokémon 1
            </label>
            <input
                type="text"
                id="pokemon_one"
                name="pokemon_one"
                value="{{ old('pokemon_one') }}"
                placeholder="ex: pikachu"
                autocomplete="off"
                class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white
                       placeholder-gray-500 px-4 py-3 text-sm
                       focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent
                       transition @error('pokemon_one') border-red-500 ring-1 ring-red-500 @enderror"
            >
            @error('pokemon_one')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Pokemon 2 --}}
        <div>
            <label for="pokemon_two" class="block text-sm font-semibold text-gray-300 mb-2">
                <span class="text-blue-400 mr-1">&#x25CF;</span> Pokémon 2
            </label>
            <input
                type="text"
                id="pokemon_two"
                name="pokemon_two"
                value="{{ old('pokemon_two') }}"
                placeholder="ex: raichu"
                autocomplete="off"
                class="w-full rounded-lg bg-gray-700 border border-gray-600 text-white
                       placeholder-gray-500 px-4 py-3 text-sm
                       focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent
                       transition @error('pokemon_two') border-red-500 ring-1 ring-red-500 @enderror"
            >
            @error('pokemon_two')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- VS divider --}}
    <div class="flex items-center gap-4 my-6">
        <div class="flex-1 h-px bg-gray-700"></div>
        <span class="text-gray-500 font-bold text-xs tracking-widest uppercase">vs</span>
        <div class="flex-1 h-px bg-gray-700"></div>
    </div>

    {{-- Submit button --}}
    <div class="text-center">
        <button type="submit" id="submit-btn"
                class="inline-flex items-center gap-2 rounded-xl bg-yellow-400 hover:bg-yellow-300
                       text-gray-900 font-bold text-base px-10 py-3
                       shadow-lg shadow-yellow-400/20 hover:shadow-yellow-300/30
                       transition-all duration-150 active:scale-95">
            <span id="btn-icon">&#x26BF;</span>
            <span id="btn-text">Batalhar!</span>
            <span id="btn-loading" class="hidden">
                <span class="pokeball"></span>
            </span>
        </button>
    </div>

</form>

{{-- ── Recent battles ── --}}
@if ($recentBattles->isNotEmpty())
<section>
    <h2 class="text-lg font-bold text-gray-300 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Últimas batalhas
    </h2>
    <div class="rounded-xl border border-gray-700 overflow-hidden shadow-lg">
        <table class="w-full text-sm">
            <thead class="bg-gray-800 text-gray-400 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Pokémon 1</th>
                    <th class="px-4 py-3 text-center">HP</th>
                    <th class="px-4 py-3 text-center">vs</th>
                    <th class="px-4 py-3 text-center">HP</th>
                    <th class="px-4 py-3 text-left">Pokémon 2</th>
                    <th class="px-4 py-3 text-center">Resultado</th>
                    <th class="px-4 py-3 text-right text-gray-600">Data</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach ($recentBattles as $battle)
                <tr class="bg-gray-900 hover:bg-gray-800 transition-colors">
                    <td class="px-4 py-3 font-medium capitalize
                        {{ $battle->result === 'pokemon_one_wins' ? 'text-yellow-400' : 'text-gray-300' }}">
                        {{ $battle->pokemon_one_name }}
                    </td>
                    <td class="px-4 py-3 text-center text-gray-400">{{ $battle->pokemon_one_hp }}</td>
                    <td class="px-4 py-3 text-center text-gray-600 font-bold text-xs">VS</td>
                    <td class="px-4 py-3 text-center text-gray-400">{{ $battle->pokemon_two_hp }}</td>
                    <td class="px-4 py-3 font-medium capitalize
                        {{ $battle->result === 'pokemon_two_wins' ? 'text-yellow-400' : 'text-gray-300' }}">
                        {{ $battle->pokemon_two_name }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($battle->result === 'draw')
                            <span class="inline-block rounded-full bg-gray-700 text-gray-400 text-xs font-semibold px-3 py-1">
                                Empate
                            </span>
                        @else
                            <span class="inline-block rounded-full bg-yellow-400/10 text-yellow-400 text-xs font-semibold px-3 py-1 capitalize">
                                {{ $battle->winner_name }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600 text-xs whitespace-nowrap">
                        {{ $battle->created_at->diffForHumans() }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@else
<div class="text-center py-12 text-gray-600">
    <p class="text-4xl mb-3 select-none">&#x26BF;</p>
    <p class="text-sm">Nenhuma batalha ainda. Seja o primeiro a lutar!</p>
</div>
@endif

@endsection

@section('head')
<script>
    document.getElementById('battle-form').addEventListener('submit', function () {
        const btn     = document.getElementById('submit-btn');
        const icon    = document.getElementById('btn-icon');
        const text    = document.getElementById('btn-text');
        const loading = document.getElementById('btn-loading');

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        icon.classList.add('hidden');
        text.textContent = 'Batalhando\u2026';
        loading.classList.remove('hidden');
    });
</script>
@endsection
