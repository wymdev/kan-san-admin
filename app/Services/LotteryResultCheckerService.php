<?php

namespace App\Services;

use App\Models\TicketPurchase;
use App\Models\DrawResult;
use Carbon\Carbon;

class LotteryResultCheckerService
{
    /**
     * Maximum days a draw can be postponed (for holidays)
     */
    const MAX_POSTPONE_DAYS = 4;

    /**
     * Check all pending purchases against latest lottery results
     */
    public function checkAllPendingPurchases()
    {
        // Get the latest draw result
        $latestDraw = DrawResult::latest('draw_date')->first();
        
        if (!$latestDraw) {
            return [
                'success' => false,
                'type' => 'error',
                'message' => '❌ No lottery draw results available in the system. Please add draw results first.',
                'checked' => 0,
                'won' => 0,
                'not_won' => 0,
                'skipped' => 0,
                'details' => []
            ];
        }

        // Get unchecked approved purchases
        $uncheckedPurchases = TicketPurchase::with(['lotteryTicket', 'customer'])
            ->where('status', TicketPurchase::STATUS_APPROVED)
            ->whereNull('checked_at')
            ->get();

        if ($uncheckedPurchases->isEmpty()) {
            return [
                'success' => true,
                'type' => 'info',
                'message' => 'ℹ️ All purchases have already been checked. No pending purchases to process.',
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
            'future_purchases' => [],
            'outdated_purchases' => [],
            'winners' => [],
        ];

        foreach ($uncheckedPurchases as $purchase) {
            $ticket = $purchase->lotteryTicket;
            
            // Skip if no ticket or no draw date
            if (!$ticket || !$ticket->withdraw_date) {
                $skippedNoTicketCount++;
                continue;
            }

            $ticketDrawDate = Carbon::parse($ticket->withdraw_date);

            // Check draw date compatibility
            $compatibility = $this->checkDrawDateCompatibility($ticketDrawDate, $actualDrawDate);

            if ($compatibility['status'] === 'future') {
                // Draw hasn't happened yet
                $skippedFutureCount++;
                $details['future_purchases'][] = [
                    'order_number' => $purchase->order_number,
                    'customer_name' => $purchase->customer->full_name ?? 'N/A',
                    'ticket_draw_date' => $ticketDrawDate->format('M d, Y'),
                    'expected_result_date' => $compatibility['expected_result_date'],
                    'days_until_draw' => $compatibility['days_difference'],
                ];
                continue;
            }

            if ($compatibility['status'] === 'outdated') {
                // Too old to check against current draw
                $skippedOutdatedCount++;
                $details['outdated_purchases'][] = [
                    'order_number' => $purchase->order_number,
                    'customer_name' => $purchase->customer->full_name ?? 'N/A',
                    'ticket_draw_date' => $ticketDrawDate->format('M d, Y'),
                    'actual_draw_date' => $actualDrawDate->format('M d, Y'),
                    'days_overdue' => $compatibility['days_difference'],
                ];
                continue;
            }

            // Check if ticket won
            $isWinner = $this->checkIfWinner($purchase, $latestDraw);

            if ($isWinner) {
                $purchase->update([
                    'status' => TicketPurchase::STATUS_WON,
                    'draw_result_id' => $latestDraw->id,
                    'prize_won' => $isWinner['prize'],
                    'checked_at' => now(),
                ]);
                $wonCount++;
                
                $details['winners'][] = [
                    'order_number' => $purchase->order_number,
                    'customer_name' => $purchase->customer->full_name ?? 'N/A',
                    'prize' => $isWinner['prize'],
                    'winning_number' => $isWinner['number'],
                ];
            } else {
                $purchase->update([
                    'status' => TicketPurchase::STATUS_NOT_WON,
                    'draw_result_id' => $latestDraw->id,
                    'checked_at' => now(),
                ]);
                $notWonCount++;
            }
        }

        $totalChecked = $wonCount + $notWonCount;
        $totalSkipped = $skippedFutureCount + $skippedOutdatedCount + $skippedNoTicketCount;

        // Build detailed message
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
            // Draw hasn't happened yet
            $daysDiff = $ticketDrawDate->diffInDays($actualDrawDate);
            $expectedDate = $ticketDrawDate->copy();
            
            // Account for possible postponement
            if ($ticketDrawDate->dayOfWeek === Carbon::SUNDAY) {
                $expectedDate->addDays(1); // Could be Monday
            }
            
            return [
                'status' => 'future',
                'days_difference' => $daysDiff,
                'expected_result_date' => $expectedDate->format('M d, Y'),
            ];
        }

        $daysDiff = $actualDrawDate->diffInDays($ticketDrawDate);

        if ($daysDiff <= self::MAX_POSTPONE_DAYS) {
            // Within acceptable range (0-4 days)
            return [
                'status' => 'compatible',
                'days_difference' => $daysDiff,
                'postponement' => $daysDiff > 0 ? "Draw postponed {$daysDiff} day(s)" : 'On time',
            ];
        }

        // Too old
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
            $parts[] = "✅ Checked <strong>{$checked}</strong> purchase(s) against draw on <strong>{$drawDate}</strong>";
            
            if ($won > 0) {
                $parts[] = "🎉 <strong>{$won}</strong> winner(s)";
            }
            
            if ($notWon > 0) {
                $parts[] = "❌ <strong>{$notWon}</strong> not won";
            }
        }

        if ($future > 0) {
            $parts[] = "⏰ <strong>{$future}</strong> purchase(s) waiting for future draw results";
        }

        if ($outdated > 0) {
            $parts[] = "⚠️ <strong>{$outdated}</strong> purchase(s) have outdated draw dates (more than 4 days old)";
        }

        if ($noTicket > 0) {
            $parts[] = "❓ <strong>{$noTicket}</strong> purchase(s) have missing ticket information";
        }

        return implode('. ', $parts) . '.';
    }

    /**
     * Check if a purchase is a winner
     */
    private function checkIfWinner(TicketPurchase $purchase, DrawResult $drawResult)
    {
        $ticket = $purchase->lotteryTicket;
        
        // Get ticket numbers (can be array or string)
        $ticketNumbers = is_array($ticket->numbers) ? $ticket->numbers : [$ticket->numbers];
        
        // Get normalized prizes using the accessor
        $prizes = $drawResult->normalized_prizes;
        
        if (empty($prizes) || !is_array($prizes)) {
            \Log::warning("DrawResult prizes is empty or not an array", [
                'draw_result_id' => $drawResult->id,
                'prizes_type' => gettype($prizes),
            ]);
            return false;
        }

        // Check each ticket number
        foreach ($ticketNumbers as $ticketNumber) {
            // Clean ticket number
            $ticketNumber = str_replace(' ', '', $ticketNumber);
            
            // Check each prize tier
            foreach ($prizes as $prizeName => $numbers) {
                // Ensure numbers is an array
                if (!is_array($numbers)) {
                    $numbers = [$numbers];
                }

                foreach ($numbers as $winningNumber) {
                    // Clean winning number
                    $winningNumber = str_replace(' ', '', $winningNumber);
                    
                    // Exact match for main prizes
                    if ($ticketNumber === $winningNumber) {
                        return [
                            'prize' => $this->formatPrizeName($prizeName),
                            'number' => $winningNumber
                        ];
                    }
                }
            }

            // Check running numbers (last 2 or 3 digits)
            $runningNumbers = $drawResult->running_numbers;
            if (is_array($runningNumbers)) {
                foreach ($runningNumbers as $runningType => $numbers) {
                    if (!is_array($numbers)) {
                        $numbers = [$numbers];
                    }

                    foreach ($numbers as $runningNumber) {
                        $runningNumber = str_replace(' ', '', $runningNumber);
                        
                        if ($this->checkRunningNumber($ticketNumber, $runningNumber)) {
                            return [
                                'prize' => $this->formatPrizeName($runningType),
                                'number' => $runningNumber
                            ];
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if two numbers match
     */
    private function numbersMatch($ticketNumber, $winningNumber)
    {
        $ticket = str_replace(' ', '', $ticketNumber);
        $winning = str_replace(' ', '', $winningNumber);
        
        return $ticket === $winning;
    }

    /**
     * Check running number (last 2 or 3 digits)
     */
    private function checkRunningNumber($ticketNumber, $runningNumber)
    {
        $ticket = str_replace(' ', '', $ticketNumber);
        $running = str_replace(' ', '', $runningNumber);
        
        $runningLength = strlen($running);
        $ticketLast = substr($ticket, -$runningLength);
        
        return $ticketLast === $running;
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
            'total_purchases' => TicketPurchase::count(),
            'awaiting_check' => TicketPurchase::where('status', TicketPurchase::STATUS_APPROVED)
                                              ->whereNull('checked_at')
                                              ->count(),
            'won' => TicketPurchase::where('status', TicketPurchase::STATUS_WON)->count(),
            'not_won' => TicketPurchase::where('status', TicketPurchase::STATUS_NOT_WON)->count(),
            'approved' => TicketPurchase::where('status', TicketPurchase::STATUS_APPROVED)->count(),
            'pending' => TicketPurchase::where('status', TicketPurchase::STATUS_PENDING)->count(),
            'rejected' => TicketPurchase::where('status', TicketPurchase::STATUS_REJECTED)->count(),
            'latest_draw_date' => $latestDraw ? Carbon::parse($latestDraw->draw_date)->format('M d, Y') : 'N/A',
            'latest_draw_date_th' => $latestDraw ? $latestDraw->date_th : 'N/A',
        ];
    }

    /**
     * Get purchases grouped by their draw date status
     */
    public function getPurchasesByDrawStatus()
    {
        $latestDraw = DrawResult::latest('draw_date')->first();
        
        if (!$latestDraw) {
            return [
                'ready_to_check' => collect(),
                'waiting_for_draw' => TicketPurchase::with(['lotteryTicket', 'customer'])
                    ->where('status', TicketPurchase::STATUS_APPROVED)
                    ->whereNull('checked_at')
                    ->get(),
                'outdated' => collect(),
                'no_draw_available' => true,
            ];
        }

        $actualDrawDate = Carbon::parse($latestDraw->draw_date);
        $unchecked = TicketPurchase::with(['lotteryTicket', 'customer'])
            ->where('status', TicketPurchase::STATUS_APPROVED)
            ->whereNull('checked_at')
            ->get();

        $readyToCheck = collect();
        $waitingForDraw = collect();
        $outdated = collect();

        foreach ($unchecked as $purchase) {
            $ticket = $purchase->lotteryTicket;
            
            if (!$ticket || !$ticket->withdraw_date) {
                $outdated->push($purchase);
                continue;
            }

            $ticketDrawDate = Carbon::parse($ticket->withdraw_date);
            $compatibility = $this->checkDrawDateCompatibility($ticketDrawDate, $actualDrawDate);

            if ($compatibility['status'] === 'future') {
                $purchase->status_info = $compatibility;
                $waitingForDraw->push($purchase);
            } elseif ($compatibility['status'] === 'compatible') {
                $purchase->status_info = $compatibility;
                $readyToCheck->push($purchase);
            } else {
                $purchase->status_info = $compatibility;
                $outdated->push($purchase);
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