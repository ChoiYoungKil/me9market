<?php

namespace App\Console\Commands;

use App\Services\CommerceAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditCommerce extends Command
{
    protected $signature = 'commerce:audit {--json : Output counts and internal IDs without customer details}';

    protected $description = 'Read-only audit of order totals, point ledgers, claim reversals and stored settlement totals';

    public function handle(CommerceAudit $audit): int
    {
        if (! Schema::hasColumns('orders_products', ['paid_line_total_snapshot', 'point_usage_snapshot', 'refund_status'])) {
            $this->error('Required audit schema is missing. Apply reviewed migrations first. No data was changed.');

            return self::FAILURE;
        }
        $result = DB::transaction(fn () => $audit->inspect());
        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info('Read-only audit: '.$result['orders'].' orders, '.$result['settlement_runs'].' settlement runs');
            $this->table(['Issue', 'Severity', 'Count', 'Sample internal IDs'], collect($result['issues'])->map(
                fn ($issue, $code) => [$code, $issue['severity'], $issue['count'], implode(', ', $issue['sample_ids'])]
            )->all());
            $this->warn($result['limitation']);
        }

        return $result['passed'] ? self::SUCCESS : self::FAILURE;
    }
}
