<?php

use App\Http\Controllers\BattleController;
use Illuminate\Support\Facades\Route;

Route::get('/',       [BattleController::class, 'index'])->name('battle.index');
Route::post('/battle', [BattleController::class, 'battle'])->name('battle.fight');
