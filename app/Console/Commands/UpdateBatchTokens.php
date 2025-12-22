<?php

namespace App\Console\Commands;

use App\Models\SecondarySalesTransaction;
use App\Models\SecondaryLotteryTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateBatchTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:update-batch-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update batch_tokens for existing transactions to ensure one link per customer per draw';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting batch token update...');

        // Get all 'own' sale type transactions without batch_token, grouped by customer + withdraw_date
        $transactions = SecondarySalesTransaction::whereNull('batch_token')
            ->where('sale_type', 'own')
            ->where(function($q) {
                $q->whereNotNull('customer_id')
                  ->orWhereNotNull('customer_phone');
            })
            ->with('secondaryTicket')
            ->get();

        $this->info("Found {$transactions->count()} transactions to process...");

        // Group by customer (ID or phone) + withdraw_date
        $groups = [];
        foreach ($transactions as $transaction) {
            $withdrawDate = $transaction->secondaryTicket?->withdraw_date?->format('Y-m-d');
            if (!$withdrawDate) continue;

            $customerKey = $transaction->customer_id 
                ? "id:{$transaction->customer_id}" 
                : "phone:{$transaction->customer_phone}";
            
            $groupKey = "{$customerKey}|{$withdrawDate}";
            
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'token' => Str::random(32),
                    'transactions' => [],
                ];
            }
            $groups[$groupKey]['transactions'][] = $transaction;
        }

        $this->info("Grouped into " . count($groups) . " customer+draw combinations...");

        // Update each group with the same batch_token
        $updatedCount = 0;
        foreach ($groups as $groupKey => $group) {
            $token = $group['token'];
            foreach ($group['transactions'] as $transaction) {
                $transaction->batch_token = $token;
                $transaction->save();
                $updatedCount++;
            }
            $this->line("  - {$groupKey}: " . count($group['transactions']) . " tickets → token: {$token}");
        }

        $this->info("✅ Updated {$updatedCount} transactions with batch_tokens!");
        $this->info("Customers now have ONE link each to check ALL their tickets for each draw date.");

        return 0;
    }
}
