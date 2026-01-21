<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SecondarySalesTransaction;
use App\Models\SecondaryLotteryTicket;
use App\Models\DrawResult;
use App\Services\LotteryResultCheckerService;
use Carbon\Carbon;

class DebugLotteryCheck extends Command
{
    protected $signature = 'lottery:debug-check {ticket_number : The 6-digit ticket number or signature}';
    protected $description = 'Debug lottery checking for a specific ticket';

    public function handle()
    {
        $ticketInput = $this->argument('ticket_number');

        // Clean the input
        $cleanNumber = preg_replace('/[^0-9]/', '', $ticketInput);

        $this->info("Searching for ticket: {$ticketInput}");

        // Find ticket by signature or number
        $ticket = SecondaryLotteryTicket::where('signature', $ticketInput)
            ->orWhere('signature', $cleanNumber)
            ->orWhereRaw("JSON_CONTAINS(numbers, ?)", [json_encode($cleanNumber)])
            ->first();

        if (!$ticket) {
            $this->error("Ticket not found: {$ticketInput}");
            $this->line("Tried searching by:");
            $this->line("  - Signature: {$ticketInput}");
            $this->line("  - Clean number: {$cleanNumber}");
            return 1;
        }

        // Find transaction
        $transaction = SecondarySalesTransaction::where('secondary_ticket_id', $ticket->id)->first();

        if (!$transaction) {
            $this->warn("Found ticket but no transaction associated");
        } else {
            $this->info("=== Transaction Info ===");
            $this->line("Transaction: {$transaction->transaction_number}");
            $this->line("Status: {$transaction->status}");
            $this->line("Prize Won: " . ($transaction->prize_won ?? 'None'));
            $this->line("");
        }

        $this->info("=== Ticket Info ===");
        $this->line("Signature: {$ticket->signature}");
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

            // Show available draws
            $nearbyDraws = DrawResult::whereBetween('draw_date', [
                $ticketDrawDate->copy()->subDays(30),
                $ticketDrawDate->copy()->addDays(30)
            ])->orderBy('draw_date')->get();

            if ($nearbyDraws->count() > 0) {
                $this->line("\nAvailable draws within ±30 days:");
                foreach ($nearbyDraws as $draw) {
                    $this->line("  - {$draw->draw_date->format('Y-m-d')} ({$draw->date_en})");
                }
            }
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
            $cleanNum = preg_replace('/[^0-9]/', '', trim($number));
            $this->line("Checking: {$cleanNum}");

            $result = $correctDraw->checkNumber($cleanNum);

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
