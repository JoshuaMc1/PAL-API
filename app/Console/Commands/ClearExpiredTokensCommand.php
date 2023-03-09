<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ClearExpiredTokensCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctum:clear-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina los tokens de Sanctum expirados';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $now = Carbon::now();

        $expirationDate = $now->subDays(31);

        $expiredTokens = DB::table('personal_access_tokens')
            ->where('created_at', '<=', $expirationDate)
            ->get();

        foreach ($expiredTokens as $token) {
            DB::table('personal_access_tokens')->where('id', $token->id)->delete();
        }

        $this->info('Se han eliminado ' . count($expiredTokens) . ' tokens expirados.');
    }
}
