<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SecondarySalesTransaction;
use App\Models\DrawResult;
use App\Services\LotteryResultCheckerService;
use Carbon\Carbon;

class DebugLotteryCheck extends Command
{
    protected $signature = 'lottery:debug-check {transaction_id}';
    protected $description = 'Debug lottery checking for a specific transaction';

    public function handle()
    {
        $transactionId = $this->argument('transaction_id');

        $transaction = SecondarySalesTransaction::with(['secondaryTicket'])->find($transactionId);

        if (!$transaction) {
            $this->error("Transaction not found: {$transactionId}");
            return 1;
        }

        $ticket = $transaction->secondaryTicket;

        if (!$ticket) {
            $this->error("No ticket found for transaction");
            return 1;
        }

        $this->info("=== Transaction Debug ===");
        $this->line("Transaction: {$transaction->transaction_number}");
        $this->line("Status: {$transaction->status}");
        $this->line("Prize Won: " . ($transaction->prize_won ?? 'None'));
        $this->line("");

        $this->info("=== Ticket Info ===");
        $this->line("Withdraw Date: {$ticket->withdraw_date}");
        $this->line("Numbers: " . json_encode($ticket->numbers));
        $this->line("");

        // Get correct draw
        $ticketDrawDate = Carbon::parse($ticket->withdraw_date);
        $this->info("=== Finding Draw Result ===");
        $this->line("Looking for draw on: {$ticketDrawDate->format('Y-m-d')}");

        $checker = app(LotteryResultCheckerService::class);
        $correctDraw = $checker->getDrawResultForDate($ticketDrawDate);

        if (!$correctDraw) {
            $this->error("No draw result found for date: {$ticketDrawDate->format('Y-m-d')}");
            return 1;
        }

        $this->line("Found Draw: {$correctDraw->date_en} (ID: {$correctDraw->id})");
        $this->line("Draw Date: {$correctDraw->draw_date}");
        $this->line("");

        $this->info("=== Draw Result Prizes ===");
        $this->line("Running Numbers:");
        $runningNumbers = $correctDraw->running_numbers;
        if (is_array($runningNumbers)) {
            foreach ($runningNumbers as $key => $running) {
                if (isset($running['id']) && isset($running['number'])) {
                    $this->line("  {$running['id']}: " . json_encode($running['number']));
                } else {
                    $this->line("  {$key}: " . json_encode($running));
                }
            }
        } else {
            $this->line("  None or invalid format");
        }
        $this->line("");

        // Check each ticket number
        $this->info("=== Checking Ticket Numbers ===");
        $ticketNumbers = is_array($ticket->numbers) ? $ticket->numbers : [$ticket->numbers];

        foreach ($ticketNumbers as $number) {
            $cleanNumber = preg_replace('/[^0-9]/', '', trim($number));
            $this->line("Checking: {$cleanNumber}");

            $result = $correctDraw->checkNumber($cleanNumber);

            if ($result && is_array($result)) {
                $this->line("  ✅ WINNER!");
                foreach ($result as $win) {
                    $this->line("    - {$win['prize_name']}: ฿" . number_format($win['reward'] ?? 0));
                }
            } else {
                $this->line("  ❌ Not a winner");
            }
            $this->line("");
        }

        return 0;
    }
}
