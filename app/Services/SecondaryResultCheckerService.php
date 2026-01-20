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
                // Enhanced prize information for Thai lottery system
                $prizeInfo = $this->formatPrizeInfo($isWinner);
                
                $transaction->update([
                    'status' => SecondarySalesTransaction::STATUS_WON,
                    'draw_result_id' => $latestDraw->id,
                    'prize_won' => $prizeInfo['display_prize'],
                    'checked_at' => now(),
                ]);
                $wonCount++;
                
                $details['winners'][] = [
                    'transaction_number' => $transaction->transaction_number,
                    'customer_name' => $transaction->customer_display_name,
                    'prize' => $prizeInfo['display_prize'],
                    'winning_number' => $isWinner['number'],
                    'total_prizes' => $isWinner['total_prizes'] ?? 1,
                    'highest_reward' => $isWinner['highest_reward'] ?? 0,
                    'all_prize_details' => $isWinner['all_wins'] ?? [],
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
     * Enhanced to match Thai lottery system exactly
     */
    private function checkIfWinner(SecondarySalesTransaction $transaction, DrawResult $drawResult)
    {
        $ticket = $transaction->secondaryTicket;
        
        // Get ticket numbers - ensure we handle different formats
        $ticketNumbers = $this->getTicketNumbers($ticket);
        if (empty($ticketNumbers)) {
            return false;
        }
        
        $allWins = [];
        $allPrizes = [];
        
        // Check each number and collect ALL possible wins
        foreach ($ticketNumbers as $ticketNumber) {
            $ticketNumber = str_replace(' ', '', trim($ticketNumber));
            
            // Validate 6-digit format (Thai lottery standard)
            if (strlen($ticketNumber) !== 6 || !ctype_digit($ticketNumber)) {
                continue;
            }
            
            $result = $drawResult->checkNumber($ticketNumber);
            
            if ($result && is_array($result)) {
                // Collect all prizes from this result
                if (isset($result[0]) && is_array($result[0])) {
                    // Multiple prizes (new format)
                    foreach ($result as $prize) {
                        $allWins[] = [
                            'won' => true,
                            'prize_id' => $prize['prize_id'] ?? 'unknown',
                            'prize_name' => $prize['prize_name'] ?? 'Prize',
                            'number' => $prize['number'] ?? $ticketNumber,
                            'full_number' => $ticketNumber,
                            'reward' => $prize['reward'] ?? 0,
                        ];
                        $allPrizes[] = $prize['prize_name'] ?? 'Prize';
                    }
                } 
                // Single prize (old format)
                elseif (isset($result['prize_name'])) {
                    $allWins[] = [
                        'won' => true,
                        'prize_id' => $result['prize_id'] ?? 'unknown',
                        'prize_name' => $result['prize_name'],
                        'number' => $result['number'] ?? $ticketNumber,
                        'full_number' => $ticketNumber,
                        'reward' => $result['reward'] ?? 0,
                    ];
                    $allPrizes[] = $result['prize_name'];
                }
            }
        }
        
        // Return comprehensive win information
        if (!empty($allWins)) {
            // Remove duplicate prizes and combine for display
            $uniquePrizes = array_unique($allPrizes);
            $prizeString = implode(', ', $uniquePrizes);
            
            return [
                'prize' => $prizeString,
                'number' => $allWins[0]['number'] ?? $ticketNumbers[0],
                'all_wins' => $allWins,
                'total_prizes' => count($uniquePrizes),
                'highest_reward' => max(array_column($allWins, 'reward')),
            ];
        }

        return false;
    }
    
    /**
     * Extract ticket numbers from different possible formats
     */
    private function getTicketNumbers($ticket)
    {
        $numbers = [];
        
        // Check if numbers field exists and is array
        if (isset($ticket->numbers) && is_array($ticket->numbers)) {
            $numbers = $ticket->numbers;
        }
        // Check if numbers field exists and is string (comma-separated)
        elseif (isset($ticket->numbers) && is_string($ticket->numbers)) {
            $numbers = explode(',', $ticket->numbers);
        }
        // Check if there's a single number field
        elseif (isset($ticket->ticket_number)) {
            $numbers = [$ticket->ticket_number];
        }
        // Fallback to any string representation
        elseif (isset($ticket)) {
            $numbers = [(string)$ticket];
        }
        
        // Clean and validate each number
        return array_filter(array_map(function($num) {
            $cleaned = preg_replace('/[^0-9]/', '', trim($num));
            return strlen($cleaned) === 6 ? $cleaned : null;
        }, $numbers));
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
     * Format prize information for Thai lottery display
     */
    private function formatPrizeInfo($winnerInfo)
    {
        $prizeString = $winnerInfo['prize'] ?? 'Unknown Prize';
        $totalPrizes = $winnerInfo['total_prizes'] ?? 1;
        $highestReward = $winnerInfo['highest_reward'] ?? 0;
        
        // Add reward amount if significant
        $displayPrize = $prizeString;
        if ($highestReward > 0) {
            $rewardFormatted = number_format($highestReward);
            $displayPrize .= " (฿{$rewardFormatted})";
        }
        
        // Add multiple prize indicator
        if ($totalPrizes > 1) {
            $displayPrize .= " [+{$totalPrizes} prizes]";
        }
        
        return [
            'display_prize' => $displayPrize,
            'total_prizes' => $totalPrizes,
            'highest_reward' => $highestReward,
        ];
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
     * Enhanced for Thai lottery system
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
                'previously_checked' => collect(),
                'can_recheck_all' => false,
            ];
        }

        $actualDrawDate = Carbon::parse($latestDraw->draw_date);
        $unchecked = SecondarySalesTransaction::with(['secondaryTicket', 'customer'])
            ->where('status', SecondarySalesTransaction::STATUS_PENDING)
            ->whereNull('checked_at')
            ->get();

        // Get previously checked transactions that can be rechecked
        $previouslyChecked = SecondarySalesTransaction::with(['secondaryTicket', 'customer', 'drawResult'])
            ->whereIn('status', [SecondarySalesTransaction::STATUS_WON, SecondarySalesTransaction::STATUS_NOT_WON])
            ->whereNotNull('checked_at')
            ->whereNotNull('draw_result_id')
            ->get();

        $readyToCheck = collect();
        $waitingForDraw = collect();
        $outdated = collect();

        foreach ($unchecked as $transaction) {
            $ticket = $transaction->secondaryTicket;
            
            // Enhanced validation for Thai lottery tickets
            if (!$ticket) {
                $transaction->validation_error = 'No ticket information found';
                $outdated->push($transaction);
                continue;
            }
            
            if (!$ticket->withdraw_date) {
                $transaction->validation_error = 'No draw date specified';
                $outdated->push($transaction);
                continue;
            }
            
            // Validate ticket numbers are in correct Thai lottery format
            $ticketNumbers = $this->getTicketNumbers($ticket);
            if (empty($ticketNumbers)) {
                $transaction->validation_error = 'Invalid or missing ticket numbers';
                $outdated->push($transaction);
                continue;
            }

            $ticketDrawDate = Carbon::parse($ticket->withdraw_date);
            $compatibility = $this->checkDrawDateCompatibility($ticketDrawDate, $actualDrawDate);

            if ($compatibility['status'] === 'future') {
                $transaction->status_info = $compatibility;
                $transaction->ticket_numbers = $ticketNumbers;
                $waitingForDraw->push($transaction);
            } elseif ($compatibility['status'] === 'compatible') {
                $transaction->status_info = $compatibility;
                $transaction->ticket_numbers = $ticketNumbers;
                
                // Pre-check for Thai lottery validation
                $preCheck = $this->thaiLotteryPreCheck($ticketNumbers, $latestDraw);
                $transaction->pre_check_result = $preCheck;
                
                $readyToCheck->push($transaction);
            } else {
                $transaction->status_info = $compatibility;
                $transaction->validation_error = 'Draw date too old (more than ' . self::MAX_POSTPONE_DAYS . ' days)';
                $outdated->push($transaction);
            }
        }

        return [
            'ready_to_check' => $readyToCheck,
            'waiting_for_draw' => $waitingForDraw,
            'outdated' => $outdated,
            'previously_checked' => $previouslyChecked,
            'can_recheck_all' => $previouslyChecked->count() > 0,
            'no_draw_available' => false,
            'latest_draw_date' => $actualDrawDate->format('M d, Y'),
            'latest_draw_data' => [
                'date' => $actualDrawDate->format('Y-m-d'),
                'has_prizes' => !empty($latestDraw->prizes),
                'has_running_numbers' => !empty($latestDraw->running_numbers),
            ],
        ];
    }

    /**
     * Recheck all previously checked transactions against latest draw results
     */
    public function recheckAllTransactions()
    {
        $latestDraw = DrawResult::latest('draw_date')->first();
        
        if (!$latestDraw) {
            return [
                'success' => false,
                'type' => 'error',
                'message' => '❌ No lottery draw results available. Please sync draw results first.',
                'rechecked' => 0,
                'changed' => 0,
                'unchanged' => 0,
                'details' => []
            ];
        }

        // Get all previously checked transactions
        $previouslyChecked = SecondarySalesTransaction::with(['secondaryTicket', 'customer', 'drawResult'])
            ->whereIn('status', [SecondarySalesTransaction::STATUS_WON, SecondarySalesTransaction::STATUS_NOT_WON])
            ->whereNotNull('checked_at')
            ->whereNotNull('draw_result_id')
            ->get();

        if ($previouslyChecked->isEmpty()) {
            return [
                'success' => true,
                'type' => 'info',
                'message' => 'ℹ️ No previously checked transactions found. Use "Check All Results" for new transactions.',
                'rechecked' => 0,
                'changed' => 0,
                'unchanged' => 0,
                'details' => []
            ];
        }

        $changedCount = 0;
        $unchangedCount = 0;
        $details = [
            'status_changed' => [],
            'prize_changed' => [],
            'new_winners' => [],
            'previous_losers' => [],
        ];

        $actualDrawDate = Carbon::parse($latestDraw->draw_date);

        foreach ($previouslyChecked as $transaction) {
            $ticket = $transaction->secondaryTicket;
            $oldStatus = $transaction->status;
            $oldPrize = $transaction->prize_won;

            // Skip if no ticket or draw info
            if (!$ticket || !$ticket->withdraw_date) {
                continue;
            }

            // Check date compatibility
            $ticketDrawDate = Carbon::parse($ticket->withdraw_date);
            $compatibility = $this->checkDrawDateCompatibility($ticketDrawDate, $actualDrawDate);

            if ($compatibility['status'] !== 'compatible') {
                continue; // Skip outdated or future transactions
            }

            // Recheck against latest draw
            $newResult = $this->checkIfWinner($transaction, $latestDraw);
            $newStatus = $newResult ? 'won' : 'not_won';
            $newPrize = $newResult ? $newResult['prize'] : null;

            // Update if status changed
            if ($oldStatus !== $newStatus || $oldPrize !== $newPrize) {
                $transaction->update([
                    'status' => $newStatus,
                    'prize_won' => $newPrize,
                    'checked_at' => now(), // Update check timestamp
                ]);

                if ($oldStatus !== $newStatus) {
                    $details['status_changed'][] = [
                        'transaction_number' => $transaction->transaction_number,
                        'customer_name' => $transaction->customer_display_name,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'ticket_number' => $ticket->ticket_number ?? 'N/A',
                    ];
                    $changedCount++;
                }

                if ($oldPrize !== $newPrize) {
                    $details['prize_changed'][] = [
                        'transaction_number' => $transaction->transaction_number,
                        'customer_name' => $transaction->customer_display_name,
                        'old_prize' => $oldPrize ?? 'None',
                        'new_prize' => $newPrize ?? 'None',
                        'ticket_number' => $ticket->ticket_number ?? 'N/A',
                    ];
                }

                // Track new winners
                if ($oldStatus === SecondarySalesTransaction::STATUS_NOT_WON && $newStatus === SecondarySalesTransaction::STATUS_WON) {
                    $details['new_winners'][] = [
                        'transaction_number' => $transaction->transaction_number,
                        'customer_name' => $transaction->customer_display_name,
                        'prize' => $newPrize,
                        'ticket_number' => $ticket->ticket_number ?? 'N/A',
                    ];
                }

                // Track previous winners who lost
                if ($oldStatus === SecondarySalesTransaction::STATUS_WON && $newStatus === SecondarySalesTransaction::STATUS_NOT_WON) {
                    $details['previous_losers'][] = [
                        'transaction_number' => $transaction->transaction_number,
                        'customer_name' => $transaction->customer_display_name,
                        'old_prize' => $oldPrize,
                        'ticket_number' => $ticket->ticket_number ?? 'N/A',
                    ];
                }
            } else {
                $unchangedCount++;
            }
        }

        $message = $this->buildRecheckMessage($changedCount, $unchangedCount, $details, $latestDraw->date_en);

        return [
            'success' => true,
            'type' => $changedCount > 0 ? 'success' : 'info',
            'message' => $message,
            'rechecked' => $previouslyChecked->count(),
            'changed' => $changedCount,
            'unchanged' => $unchangedCount,
            'details' => $details,
            'draw_date' => $latestDraw->date_en,
        ];
    }

    /**
     * Recheck specific transactions
     */
    public function recheckTransactions(array $transactionIds)
    {
        $latestDraw = DrawResult::latest('draw_date')->first();
        
        if (!$latestDraw) {
            return [
                'success' => false,
                'type' => 'error',
                'message' => '❌ No lottery draw results available. Please sync draw results first.',
                'rechecked' => 0,
                'changed' => 0,
                'details' => []
            ];
        }

        $transactions = SecondarySalesTransaction::with(['secondaryTicket', 'customer', 'drawResult'])
            ->whereIn('id', $transactionIds)
            ->whereIn('status', [SecondarySalesTransaction::STATUS_WON, SecondarySalesTransaction::STATUS_NOT_WON])
            ->get();

        if ($transactions->isEmpty()) {
            return [
                'success' => false,
                'type' => 'error',
                'message' => '❌ No valid transactions found for rechecking.',
                'rechecked' => 0,
                'changed' => 0,
                'details' => []
            ];
        }

        return $this->performRecheck($transactions, $latestDraw);
    }

    /**
     * Perform the actual recheck operation
     */
    private function performRecheck($transactions, $drawResult)
    {
        $changedCount = 0;
        $details = [
            'status_changed' => [],
            'prize_changed' => [],
            'new_winners' => [],
            'previous_losers' => [],
        ];

        foreach ($transactions as $transaction) {
            $oldStatus = $transaction->status;
            $oldPrize = $transaction->prize_won;

            $newResult = $this->checkIfWinner($transaction, $drawResult);
            $newStatus = $newResult ? SecondarySalesTransaction::STATUS_WON : SecondarySalesTransaction::STATUS_NOT_WON;
            $newPrize = $newResult ? $newResult['prize'] : null;

            // Update if anything changed
            if ($oldStatus !== $newStatus || $oldPrize !== $newPrize) {
                $transaction->update([
                    'status' => $newStatus,
                    'prize_won' => $newPrize,
                    'checked_at' => now(),
                ]);

                if ($oldStatus !== $newStatus) {
                    $details['status_changed'][] = [
                        'transaction_number' => $transaction->transaction_number,
                        'customer_name' => $transaction->customer_display_name,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'ticket_number' => $transaction->secondaryTicket?->ticket_number ?? 'N/A',
                    ];
                    $changedCount++;
                }

                if ($oldPrize !== $newPrize) {
                    $details['prize_changed'][] = [
                        'transaction_number' => $transaction->transaction_number,
                        'customer_name' => $transaction->customer_display_name,
                        'old_prize' => $oldPrize ?? 'None',
                        'new_prize' => $newPrize ?? 'None',
                        'ticket_number' => $transaction->secondaryTicket?->ticket_number ?? 'N/A',
                    ];
                }
            }
        }

        return [
            'success' => true,
            'rechecked' => $transactions->count(),
            'changed' => $changedCount,
            'details' => $details,
            'draw_date' => $drawResult->date_en,
        ];
    }

    /**
     * Build recheck result message
     */
    private function buildRecheckMessage($changed, $unchanged, $details, $drawDate)
    {
        $parts = [];
        
        $parts[] = "✅ Rechecked <strong>" . ($changed + $unchanged) . "</strong> transaction(s) against draw on <strong>{$drawDate}</strong>";
        
        if ($changed > 0) {
            $parts[] = "🔄 <strong>{$changed}</strong> transaction(s) had status changes";
            
            if (!empty($details['new_winners'])) {
                $parts[] = "🎉 <strong>" . count($details['new_winners']) . "</strong> new winner(s)";
            }
            
            if (!empty($details['previous_losers'])) {
                $parts[] = "😔 <strong>" . count($details['previous_losers']) . "</strong> previous winner(s) lost";
            }
        }
        
        if ($unchanged > 0) {
            $parts[] = "✅ <strong>{$unchanged}</strong> transaction(s) unchanged";
        }

        return implode('. ', $parts) . '.';
    }
    
    /**
     * Pre-check ticket numbers against Thai lottery rules
     */
    private function thaiLotteryPreCheck($ticketNumbers, DrawResult $drawResult)
    {
        $validNumbers = [];
        $potentialWins = [];
        
        foreach ($ticketNumbers as $ticketNumber) {
            // Validate Thai lottery format
            if (strlen($ticketNumber) !== 6 || !ctype_digit($ticketNumber)) {
                $validNumbers[] = $ticketNumber . ' (invalid)';
                continue;
            }
            
            $validNumbers[] = $ticketNumber;
            
            // Quick check against available prizes
            $result = $drawResult->checkNumber($ticketNumber);
            if ($result && is_array($result)) {
                $prizeCount = is_array($result[0]) ? count($result) : 1;
                $potentialWins[] = $ticketNumber . " ({$prizeCount} possible)";
            } else {
                $potentialWins[] = $ticketNumber . ' (no match)';
            }
        }
        
        return [
            'valid_numbers' => $validNumbers,
            'invalid_numbers' => array_diff($ticketNumbers, $validNumbers),
            'potential_wins' => $potentialWins,
            'checkable_count' => count($validNumbers),
        ];
    }
}
