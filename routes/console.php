<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('clear-expired-tokens', function () {
    $now = Carbon::now();

    $expirationDate = $now;

    $expiredTokens = DB::table('personal_access_tokens')
        ->where('expires_at', '<=', $expirationDate)
        ->get();

    foreach ($expiredTokens as $token) {
        DB::table('personal_access_tokens')->where('id', $token->id)->delete();
    }

    $this->info('Se han eliminado ' . count($expiredTokens) . ' tokens expirados.');
})->describe('Elimina los tokens de Sanctum expirados');
