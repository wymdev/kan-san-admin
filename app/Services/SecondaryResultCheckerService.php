<?php

namespace App\Services;

use App\Models\SecondarySalesTransaction;
use App\Models\DrawResult;
use Carbon\Carbon;

class SecondaryResultCheckerService
{
    /**
     * Maximum days a draw can be postponed (for holidays)
     */
    const MAX_POSTPONE_DAYS = 4;

    /**
     * Check all pending secondary transactions against latest lottery results
     */
    public function checkAllPendingTransactions()
    {
        // Get the latest draw result
        $latestDraw = DrawResult::latest('draw_date')->first();
        
        if (!$latestDraw) {
            return [
                'success' => false,
                'type' => 'error',
                'message' => '❌ No lottery draw results available. Please sync draw results first.',
                'checked' => 0,
                'won' => 0,
                'not_won' => 0,
                'skipped' => 0,
                'details' => []
            ];
        }

        // Get unchecked transactions
        $uncheckedTransactions = SecondarySalesTransaction::with(['secondaryTicket', 'customer'])
            ->where('status', SecondarySalesTransaction::STATUS_PENDING)
            ->whereNull('checked_at')
            ->get();

        if ($uncheckedTransactions->isEmpty()) {
            return [
                'success' => true,
                'type' => 'info',
                'message' => 'ℹ️ All transactions have been checked. No pending transactions.',
                'checked' => 0,
                'won' => 0,
                'not_won' => 0,
                'skipped' => 0,
                'draw_date' => $latestDraw->date_en,
                'details' => []
            ];
        }

        $wonCount = 0;
        $notWonCount = 0;
        $skippedFutureCount = 0;
        $skippedOutdatedCount = 0;
        $skippedNoTicketCount = 0;
        $actualDrawDate = Carbon::parse($latestDraw->draw_date);
        
        $details = [
            'future_transactions' => [],
            'outdated_transactions' => [],
            'winners' => [],
        ];

        foreach ($uncheckedTransactions as $transaction) {
            $ticket = $transaction->secondaryTicket;
            
            // Skip if no ticket or no draw date
            if (!$ticket || !$ticket->withdraw_date) {
                $skippedNoTicketCount++;
                continue;
            }

            $ticketDrawDate = Carbon::parse($ticket->withdraw_date);

            // Check draw date compatibility
            $compatibility = $this->checkDrawDateCompatibility($ticketDrawDate, $actualDrawDate);

            if ($compatibility['status'] === 'future') {
                $skippedFutureCount++;
                $details['future_transactions'][] = [
                    'transaction_number' => $transaction->transaction_number,
                    'customer_name' => $transaction->customer_display_name,
                    'ticket_draw_date' => $ticketDrawDate->format('M d, Y'),
                    'days_until_draw' => $compatibility['days_difference'],
                ];
                continue;
            }

            if ($compatibility['status'] === 'outdated') {
                $skippedOutdatedCount++;
                $details['outdated_transactions'][] = [
                    'transaction_number' => $transaction->transaction_number,
                    'customer_name' => $transaction->customer_display_name,
                    'ticket_draw_date' => $ticketDrawDate->format('M d, Y'),
                    'days_overdue' => $compatibility['days_difference'],
                ];
                continue;
            }

            // Check if ticket won
            $isWinner = $this->checkIfWinner($transaction, $latestDraw);

            if ($isWinner) {
                $transaction->update([
                    'status' => SecondarySalesTransaction::STATUS_WON,
                    'draw_result_id' => $latestDraw->id,
                    'prize_won' => $isWinner['prize'],
                    'checked_at' => now(),
                ]);
                $wonCount++;
                
                $details['winners'][] = [
                    'transaction_number' => $transaction->transaction_number,
                    'customer_name' => $transaction->customer_display_name,
                    'prize' => $isWinner['prize'],
                    'winning_number' => $isWinner['number'],
                ];
            } else {
                $transaction->update([
                    'status' => SecondarySalesTransaction::STATUS_NOT_WON,
                    'draw_result_id' => $latestDraw->id,
                    'checked_at' => now(),
                ]);
                $notWonCount++;
            }
        }

        $totalChecked = $wonCount + $notWonCount;
        $totalSkipped = $skippedFutureCount + $skippedOutdatedCount + $skippedNoTicketCount;

        $message = $this->buildResultMessage(
            $totalChecked,
            $wonCount,
            $notWonCount,
            $skippedFutureCount,
            $skippedOutdatedCount,
            $skippedNoTicketCount,
            $latestDraw->date_en
        );

        return [
            'success' => true,
            'type' => $totalChecked > 0 ? 'success' : 'warning',
            'message' => $message,
            'checked' => $totalChecked,
            'won' => $wonCount,
            'not_won' => $notWonCount,
            'skipped' => $totalSkipped,
            'skipped_future' => $skippedFutureCount,
            'skipped_outdated' => $skippedOutdatedCount,
            'skipped_no_ticket' => $skippedNoTicketCount,
            'draw_date' => $latestDraw->date_en,
            'actual_draw_date' => $actualDrawDate->format('M d, Y'),
            'details' => $details,
        ];
    }

    /**
     * Check draw date compatibility
     */
    private function checkDrawDateCompatibility(Carbon $ticketDrawDate, Carbon $actualDrawDate)
    {
        if ($actualDrawDate->lt($ticketDrawDate)) {
            $daysDiff = $ticketDrawDate->diffInDays($actualDrawDate);
            
            return [
                'status' => 'future',
                'days_difference' => $daysDiff,
            ];
        }

        $daysDiff = $actualDrawDate->diffInDays($ticketDrawDate);

        if ($daysDiff <= self::MAX_POSTPONE_DAYS) {
            return [
                'status' => 'compatible',
                'days_difference' => $daysDiff,
            ];
        }

        return [
            'status' => 'outdated',
            'days_difference' => $daysDiff,
        ];
    }

    /**
     * Build result message
     */
    private function buildResultMessage($checked, $won, $notWon, $future, $outdated, $noTicket, $drawDate)
    {
        $parts = [];

        if ($checked > 0) {
            $parts[] = "✅ Checked <strong>{$checked}</strong> transaction(s) against draw on <strong>{$drawDate}</strong>";
            
            if ($won > 0) {
                $parts[] = "🎉 <strong>{$won}</strong> winner(s)";
            }
            
            if ($notWon > 0) {
                $parts[] = "❌ <strong>{$notWon}</strong> not won";
            }
        }

        if ($future > 0) {
            $parts[] = "⏰ <strong>{$future}</strong> transaction(s) waiting for future draw results";
        }

        if ($outdated > 0) {
            $parts[] = "⚠️ <strong>{$outdated}</strong> transaction(s) have outdated draw dates";
        }

        if ($noTicket > 0) {
            $parts[] = "❓ <strong>{$noTicket}</strong> transaction(s) have missing ticket information";
        }

        return implode('. ', $parts) . '.';
    }

    /**
     * Check if a transaction is a winner - supports multiple prizes per ticket
     */
    private function checkIfWinner(SecondarySalesTransaction $transaction, DrawResult $drawResult)
    {
        $ticket = $transaction->secondaryTicket;
        
        // Get ticket numbers
        $ticketNumbers = is_array($ticket->numbers) ? $ticket->numbers : [$ticket->numbers];
        if (empty($ticketNumbers)) {
            return false;
        }
        
        // Check each number
        foreach ($ticketNumbers as $ticketNumber) {
            $ticketNumber = str_replace(' ', '', $ticketNumber);
            
            $result = $drawResult->checkNumber($ticketNumber);
            
            if ($result && is_array($result) && count($result) > 0) {
                // New format: array of prize arrays
                if (isset($result[0]) && is_array($result[0])) {
                    // Combine all prize names
                    $prizes = array_map(fn($p) => $p['prize_name'] ?? 'Prize', $result);
                    return [
                        'prize' => implode(', ', $prizes),
                        'number' => $result[0]['number'] ?? $ticketNumber,
                        'all_prizes' => $result,
                    ];
                }
                // Old format (single prize)
                elseif (isset($result['prize_name'])) {
                    return [
                        'prize' => $result['prize_name'],
                        'number' => $result['number'] ?? $ticketNumber,
                    ];
                }
            }
        }

        return false;
    }

    /**
     * Check running number (last 2 or 3 digits)
     */
    private function checkRunningNumber($ticketNumber, $runningNumber)
    {
        $runningLength = strlen($runningNumber);
        $ticketLast = substr($ticketNumber, -$runningLength);
        
        return $ticketLast === $runningNumber;
    }

    /**
     * Format prize name for display
     */
    private function formatPrizeName($prizeName)
    {
        $nameMap = [
            'first_prize' => '1st Prize 🥇',
            'second_prize' => '2nd Prize 🥈',
            'third_prize' => '3rd Prize 🥉',
            'fourth_prize' => '4th Prize',
            'fifth_prize' => '5th Prize',
            'near_first_prize' => 'Near 1st Prize',
            'running_3digits_front' => '3-Digit Front',
            'running_3digits_back' => '3-Digit Back',
            'running_2digits' => '2-Digit',
        ];

        return $nameMap[$prizeName] ?? ucwords(str_replace('_', ' ', $prizeName));
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics()
    {
        $latestDraw = DrawResult::latest('draw_date')->first();
        
        return [
            'total_transactions' => SecondarySalesTransaction::count(),
            'total_revenue' => SecondarySalesTransaction::sum('amount_thb'),
            'total_revenue_mmk' => SecondarySalesTransaction::sum('amount_mmk'),
            'awaiting_check' => SecondarySalesTransaction::where('status', SecondarySalesTransaction::STATUS_PENDING)
                                              ->whereNull('checked_at')
                                              ->count(),
            'won' => SecondarySalesTransaction::where('status', SecondarySalesTransaction::STATUS_WON)->count(),
            'not_won' => SecondarySalesTransaction::where('status', SecondarySalesTransaction::STATUS_NOT_WON)->count(),
            'pending' => SecondarySalesTransaction::where('status', SecondarySalesTransaction::STATUS_PENDING)->count(),
            'paid' => SecondarySalesTransaction::where('is_paid', true)->count(),
            'unpaid' => SecondarySalesTransaction::where('is_paid', false)->count(),
            'unpaid_amount' => SecondarySalesTransaction::where('is_paid', false)->sum('amount_thb'),
            'unpaid_amount_mmk' => SecondarySalesTransaction::where('is_paid', false)->sum('amount_mmk'),
            'latest_draw_date' => $latestDraw ? Carbon::parse($latestDraw->draw_date)->format('M d, Y') : 'N/A',
        ];
    }

    /**
     * Get transactions grouped by their draw date status
     */
    public function getTransactionsByDrawStatus()
    {
        $latestDraw = DrawResult::latest('draw_date')->first();
        
        if (!$latestDraw) {
            return [
                'ready_to_check' => collect(),
                'waiting_for_draw' => SecondarySalesTransaction::with(['secondaryTicket', 'customer'])
                    ->where('status', SecondarySalesTransaction::STATUS_PENDING)
                    ->whereNull('checked_at')
                    ->get(),
                'outdated' => collect(),
                'no_draw_available' => true,
            ];
        }

        $actualDrawDate = Carbon::parse($latestDraw->draw_date);
        $unchecked = SecondarySalesTransaction::with(['secondaryTicket', 'customer'])
            ->where('status', SecondarySalesTransaction::STATUS_PENDING)
            ->whereNull('checked_at')
            ->get();

        $readyToCheck = collect();
        $waitingForDraw = collect();
        $outdated = collect();

        foreach ($unchecked as $transaction) {
            $ticket = $transaction->secondaryTicket;
            
            if (!$ticket || !$ticket->withdraw_date) {
                $outdated->push($transaction);
                continue;
            }

            $ticketDrawDate = Carbon::parse($ticket->withdraw_date);
            $compatibility = $this->checkDrawDateCompatibility($ticketDrawDate, $actualDrawDate);

            if ($compatibility['status'] === 'future') {
                $transaction->status_info = $compatibility;
                $waitingForDraw->push($transaction);
            } elseif ($compatibility['status'] === 'compatible') {
                $transaction->status_info = $compatibility;
                $readyToCheck->push($transaction);
            } else {
                $transaction->status_info = $compatibility;
                $outdated->push($transaction);
            }
        }

        return [
            'ready_to_check' => $readyToCheck,
            'waiting_for_draw' => $waitingForDraw,
            'outdated' => $outdated,
            'no_draw_available' => false,
            'latest_draw_date' => $actualDrawDate->format('M d, Y'),
        ];
    }
}
