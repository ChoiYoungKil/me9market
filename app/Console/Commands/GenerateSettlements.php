<?php

namespace App\Console\Commands;

use App\Services\SettlementCalculator;
use Illuminate\Console\Command;

class GenerateSettlements extends Command
{
    protected $signature = 'settlements:generate {period? : Settlement period in YYYY-MM format}';

    protected $description = 'Generate or refresh pending settlement data';

    public function handle(SettlementCalculator $calculator): int
    {
        $period = $calculator->normalizePeriod($this->argument('period'));
        $runs = $calculator->generate($period);

        $this->info($period.' settlement records generated: '.$runs->count());

        return self::SUCCESS;
    }
}
