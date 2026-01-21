<?php

namespace App\Services;

use App\Models\TicketPurchase;
use App\Models\SecondarySalesTransaction;
use App\Models\DrawResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LotteryResultCheckerService
{
    /**
     * Maximum days a draw can be postponed (for holidays)
     */
    const MAX_POSTPONE_DAYS = 4;

    /**
     * Cache TTL for draw results (1 hour)
     */
    const CACHE_TTL = 3600;

    // =========================================================================
    // CORE CHECKING METHODS (Shared by Primary & Secondary)
    // =========================================================================

    /**
     * Check a ticket number against a specific draw result
     * Returns all matching prizes or false
     */
    public function checkTicketNumber(string $ticketNumber, DrawResult $drawResult): array|false
    {
        $ticketNumber = str_replace(' ', '', trim($ticketNumber));

        // Validate 6-digit format (Thai lottery standard)
        if (strlen($ticketNumber) !== 6 || !ctype_digit($ticketNumber)) {
            return false;
        }

        $result = $drawResult->checkNumber($ticketNumber);

        if ($result && is_array($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Check multiple ticket numbers against a draw result
     * Returns all wins for all numbers
     */
    public function checkMultipleNumbers(array $ticketNumbers, DrawResult $drawResult): array
    {
        $allWins = [];

        foreach ($ticketNumbers as $ticketNumber) {
            $result = $this->checkTicketNumber($ticketNumber, $drawResult);
            if ($result) {
                foreach ($result as $win) {
                    $win['checked_number'] = $ticketNumber;
                    $allWins[] = $win;
                }
            }
        }

        return $allWins;
    }

    /**
     * Get the appropriate draw result for a given date
     * Supports historical lookups based on Thai lottery schedule (1st and 16th of month)
     */
    public function getDrawResultForDate(Carbon $targetDate): ?DrawResult
    {
        $cacheKey = 'draw_result_date_' . $targetDate->format('Y-m-d');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($targetDate) {
            // First try exact match
            $drawResult = DrawResult::whereDate('draw_date', $targetDate)->first();

            if ($drawResult) {
                return $drawResult;
            }

            // Try within postpone tolerance (e.g., draw on holiday moved to next day)
            $drawResult = DrawResult::whereBetween('draw_date', [
                $targetDate->copy()->subDays(self::MAX_POSTPONE_DAYS),
                $targetDate->copy()->addDays(self::MAX_POSTPONE_DAYS),
            ])
                ->orderByRaw("ABS(DATEDIFF(draw_date, ?))", [$targetDate])
                ->first();

            if ($drawResult) {
                return $drawResult;
            }

            // Thai lottery draws on 1st and 16th - find the nearest past draw
            // Get the draw that should cover this ticket date
            $drawResult = DrawResult::where('draw_date', '<=', $targetDate)
                ->orderBy('draw_date', 'desc')
                ->first();

            return $drawResult;
        });
    }

    /**
     * Get the latest draw result (cached)
     */
    public function getLatestDrawResult(): ?DrawResult
    {
        return Cache::remember('latest_draw_result', self::CACHE_TTL, function () {
            return DrawResult::latest('draw_date')->first();
        });
    }

    /**
     * Clear draw result cache
     */
    public function clearCache(): void
    {
        Cache::forget('latest_draw_result');
    }

    // =========================================================================
    // PRIMARY TICKET PURCHASES (Original logic preserved)
    // =========================================================================

    /**
     * Check all pending purchases against lottery results
     */
    public function checkAllPendingPurchases(): array
    {
        $latestDraw = $this->getLatestDrawResult();

        if (!$latestDraw) {
            return $this->errorResponse('No lottery draw results available in the system.');
        }

        $uncheckedPurchases = TicketPurchase::with(['lotteryTicket', 'customer'])
            ->where('status', TicketPurchase::STATUS_APPROVED)
            ->whereNull('checked_at')
            ->get();

        if ($uncheckedPurchases->isEmpty()) {
            return $this->infoResponse('All purchases have already been checked.', $latestDraw->date_en);
        }

        return $this->processPurchases($uncheckedPurchases, $latestDraw);
    }

    /**
     * Process a collection of purchases against a draw result
     */
    private function processPurchases($purchases, DrawResult $drawResult): array
    {
        $wonCount = 0;
        $notWonCount = 0;
        $skippedFutureCount = 0;
        $skippedOutdatedCount = 0;
        $skippedNoTicketCount = 0;
        $actualDrawDate = Carbon::parse($drawResult->draw_date);

        $details = [
            'future_purchases' => [],
            'outdated_purchases' => [],
            'winners' => [],
        ];

        foreach ($purchases as $purchase) {
            $ticket = $purchase->lotteryTicket;

            if (!$ticket || !$ticket->withdraw_date) {
                $skippedNoTicketCount++;
                continue;
            }

            $ticketDrawDate = Carbon::parse($ticket->withdraw_date);

            // Find the correct draw result for this ticket's date (historical lookup)
            $correctDraw = $this->getDrawResultForDate($ticketDrawDate);

            if (!$correctDraw) {
                // No draw result exists for this date yet
                $skippedFutureCount++;
                $details['future_purchases'][] = [
                    'order_number' => $purchase->order_number,
                    'customer_name' => $purchase->customer->full_name ?? 'N/A',
                    'ticket_draw_date' => $ticketDrawDate->format('M d, Y'),
                    'days_until_draw' => $ticketDrawDate->diffInDays(now()),
                ];
                continue;
            }

            // Check compatibility between ticket date and draw result date
            $compatibility = $this->checkDrawDateCompatibility($ticketDrawDate, Carbon::parse($correctDraw->draw_date));

            if ($compatibility['status'] === 'future') {
                $skippedFutureCount++;
                $details['future_purchases'][] = [
                    'order_number' => $purchase->order_number,
                    'customer_name' => $purchase->customer->full_name ?? 'N/A',
                    'ticket_draw_date' => $ticketDrawDate->format('M d, Y'),
                    'days_until_draw' => $compatibility['days_difference'],
                ];
                continue;
            }

            if ($compatibility['status'] === 'outdated') {
                $skippedOutdatedCount++;
                $details['outdated_purchases'][] = [
                    'order_number' => $purchase->order_number,
                    'customer_name' => $purchase->customer->full_name ?? 'N/A',
                    'ticket_draw_date' => $ticketDrawDate->format('M d, Y'),
                    'days_overdue' => $compatibility['days_difference'],
                ];
                continue;
            }

            // Check if ticket won
            $winResult = $this->checkPurchaseAgainstDraw($purchase, $correctDraw);

            if ($winResult) {
                $purchase->update([
                    'status' => TicketPurchase::STATUS_WON,
                    'draw_result_id' => $correctDraw->id,
                    'prize_won' => $winResult['prize'],
                    'checked_at' => now(),
                ]);
                $wonCount++;

                $details['winners'][] = [
                    'order_number' => $purchase->order_number,
                    'customer_name' => $purchase->customer->full_name ?? 'N/A',
                    'prize' => $winResult['prize'],
                    'winning_number' => $winResult['number'],
                    'total_prizes' => $winResult['total_prizes'] ?? 1,
                ];
            } else {
                $purchase->update([
                    'status' => TicketPurchase::STATUS_NOT_WON,
                    'draw_result_id' => $correctDraw->id,
                    'checked_at' => now(),
                ]);
                $notWonCount++;
            }
        }

        return $this->buildSuccessResponse(
            $wonCount,
            $notWonCount,
            $skippedFutureCount,
            $skippedOutdatedCount,
            $skippedNoTicketCount,
            $drawResult->date_en,
            $details,
            'purchase'
        );
    }

    /**
     * Check a single purchase against a draw result
     */
    private function checkPurchaseAgainstDraw(TicketPurchase $purchase, DrawResult $drawResult): array|false
    {
        $ticket = $purchase->lotteryTicket;
        $ticketNumbers = is_array($ticket->numbers) ? $ticket->numbers : [$ticket->numbers];

        $allWins = $this->checkMultipleNumbers($ticketNumbers, $drawResult);

        if (!empty($allWins)) {
            return $this->formatWinResult($allWins);
        }

        return false;
    }

    // =========================================================================
    // SECONDARY SALES TRANSACTIONS (Migrated from SecondaryResultCheckerService)
    // =========================================================================

    /**
     * Check all pending secondary transactions against lottery results
     */
    public function checkAllPendingTransactions(): array
    {
        $latestDraw = $this->getLatestDrawResult();

        if (!$latestDraw) {
            return $this->errorResponse('No lottery draw results available. Please sync draw results first.');
        }

        $uncheckedTransactions = SecondarySalesTransaction::with(['secondaryTicket', 'customer'])
            ->where('status', SecondarySalesTransaction::STATUS_PENDING)
            ->whereNull('checked_at')
            ->get();

        if ($uncheckedTransactions->isEmpty()) {
            return $this->infoResponse('All transactions have been checked. No pending transactions.', $latestDraw->date_en);
        }

        return $this->processTransactions($uncheckedTransactions, $latestDraw);
    }

    /**
     * Process a collection of secondary transactions against a draw result
     */
    private function processTransactions($transactions, DrawResult $latestDraw): array
    {
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

        foreach ($transactions as $transaction) {
            $ticket = $transaction->secondaryTicket;

            if (!$ticket || !$ticket->withdraw_date) {
                $skippedNoTicketCount++;
                continue;
            }

            $ticketDrawDate = Carbon::parse($ticket->withdraw_date);

            // Find the correct draw result for this ticket's date (historical lookup)
            $correctDraw = $this->getDrawResultForDate($ticketDrawDate);

            if (!$correctDraw) {
                $skippedFutureCount++;
                $details['future_transactions'][] = [
                    'transaction_number' => $transaction->transaction_number,
                    'customer_name' => $transaction->customer_display_name,
                    'ticket_draw_date' => $ticketDrawDate->format('M d, Y'),
                    'days_until_draw' => $ticketDrawDate->diffInDays(now()),
                ];
                continue;
            }

            $compatibility = $this->checkDrawDateCompatibility($ticketDrawDate, Carbon::parse($correctDraw->draw_date));

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
            $winResult = $this->checkTransactionAgainstDraw($transaction, $correctDraw);

            if ($winResult) {
                $prizeInfo = $this->formatPrizeInfo($winResult);

                $transaction->update([
                    'status' => SecondarySalesTransaction::STATUS_WON,
                    'draw_result_id' => $correctDraw->id,
                    'prize_won' => $prizeInfo['display_prize'],
                    'checked_at' => now(),
                ]);
                $wonCount++;

                $details['winners'][] = [
                    'transaction_number' => $transaction->transaction_number,
                    'customer_name' => $transaction->customer_display_name,
                    'prize' => $prizeInfo['display_prize'],
                    'winning_number' => $winResult['number'],
                    'total_prizes' => $winResult['total_prizes'] ?? 1,
                    'highest_reward' => $winResult['highest_reward'] ?? 0,
                    'all_prize_details' => $winResult['all_wins'] ?? [],
                ];
            } else {
                $transaction->update([
                    'status' => SecondarySalesTransaction::STATUS_NOT_WON,
                    'draw_result_id' => $correctDraw->id,
                    'checked_at' => now(),
                ]);
                $notWonCount++;
            }
        }

        return $this->buildSuccessResponse(
            $wonCount,
            $notWonCount,
            $skippedFutureCount,
            $skippedOutdatedCount,
            $skippedNoTicketCount,
            $latestDraw->date_en,
            $details,
            'transaction'
        );
    }

    /**
     * Check a single secondary transaction against a draw result
     */
    private function checkTransactionAgainstDraw(SecondarySalesTransaction $transaction, DrawResult $drawResult): array|false
    {
        $ticket = $transaction->secondaryTicket;
        $ticketNumbers = $this->getTicketNumbers($ticket);

        if (empty($ticketNumbers)) {
            return false;
        }

        $allWins = $this->checkMultipleNumbers($ticketNumbers, $drawResult);

        if (!empty($allWins)) {
            return $this->formatWinResult($allWins);
        }

        return false;
    }

    /**
     * Recheck all previously checked transactions against latest draw
     */
    public function recheckAllTransactions(): array
    {
        $latestDraw = $this->getLatestDrawResult();

        if (!$latestDraw) {
            return $this->errorResponse('No lottery draw results available. Please sync draw results first.');
        }

        $previouslyChecked = SecondarySalesTransaction::with(['secondaryTicket', 'customer', 'drawResult'])
            ->whereIn('status', [SecondarySalesTransaction::STATUS_WON, SecondarySalesTransaction::STATUS_NOT_WON])
            ->whereNotNull('checked_at')
            ->whereNotNull('draw_result_id')
            ->get();

        if ($previouslyChecked->isEmpty()) {
            return $this->infoResponse('No previously checked transactions found.', $latestDraw->date_en);
        }

        $changedCount = 0;
        $unchangedCount = 0;
        $details = [
            'status_changed' => [],
            'prize_changed' => [],
            'new_winners' => [],
            'previous_losers' => [],
        ];

        foreach ($previouslyChecked as $transaction) {
            $ticket = $transaction->secondaryTicket;
            $oldStatus = $transaction->status;
            $oldPrize = $transaction->prize_won;

            if (!$ticket || !$ticket->withdraw_date) {
                continue;
            }

            $ticketDrawDate = Carbon::parse($ticket->withdraw_date);
            $correctDraw = $this->getDrawResultForDate($ticketDrawDate);

            if (!$correctDraw) {
                continue;
            }

            $compatibility = $this->checkDrawDateCompatibility($ticketDrawDate, Carbon::parse($correctDraw->draw_date));

            if ($compatibility['status'] !== 'compatible') {
                continue;
            }

            $newResult = $this->checkTransactionAgainstDraw($transaction, $correctDraw);
            $newStatus = $newResult ? 'won' : 'not_won';
            $newPrize = $newResult ? $this->formatPrizeInfo($newResult)['display_prize'] : null;

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
                    ];
                    $changedCount++;
                }
            } else {
                $unchangedCount++;
            }
        }

        return [
            'success' => true,
            'type' => $changedCount > 0 ? 'success' : 'info',
            'message' => $this->buildRecheckMessage($changedCount, $unchangedCount, $details, 'historical draws'),
            'rechecked' => $previouslyChecked->count(),
            'changed' => $changedCount,
            'unchanged' => $unchangedCount,
            'details' => $details,
            'draw_date' => 'Multiple historical draws checked',
        ];
    }

    /**
     * Recheck specific transactions by IDs
     */
    public function recheckTransactions(array $transactionIds): array
    {
        $latestDraw = $this->getLatestDrawResult();

        if (!$latestDraw) {
            return $this->errorResponse('No lottery draw results available.');
        }

        $transactions = SecondarySalesTransaction::with(['secondaryTicket', 'customer'])
            ->whereIn('id', $transactionIds)
            ->whereIn('status', [SecondarySalesTransaction::STATUS_WON, SecondarySalesTransaction::STATUS_NOT_WON])
            ->get();

        if ($transactions->isEmpty()) {
            return $this->errorResponse('No valid transactions found for rechecking.');
        }

        return $this->performRecheck($transactions, $latestDraw);
    }

    // =========================================================================
    // STATISTICS & DASHBOARD
    // =========================================================================

    /**
     * Get primary purchase statistics
     */
    public function getPurchaseStatistics(): array
    {
        $latestDraw = $this->getLatestDrawResult();

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
        ];
    }

    /**
     * Get secondary transaction statistics
     */
    public function getTransactionStatistics(): array
    {
        $latestDraw = $this->getLatestDrawResult();

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
            'latest_draw_date' => $latestDraw ? Carbon::parse($latestDraw->draw_date)->format('M d, Y') : 'N/A',
        ];
    }

    /**
     * Get transactions grouped by draw status (for UI display)
     */
    public function getTransactionsByDrawStatus(): array
    {
        $latestDraw = $this->getLatestDrawResult();

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

        $previouslyChecked = SecondarySalesTransaction::with(['secondaryTicket', 'customer', 'drawResult'])
            ->whereIn('status', ['won', 'not_won'])
            ->whereNotNull('checked_at')
            ->whereNotNull('draw_result_id')
            ->get();

        $readyToCheck = collect();
        $waitingForDraw = collect();
        $outdated = collect();

        foreach ($unchecked as $transaction) {
            $ticket = $transaction->secondaryTicket;

            if (!$ticket || !$ticket->withdraw_date) {
                $transaction->validation_error = 'No ticket information found';
                $outdated->push($transaction);
                continue;
            }

            $ticketDrawDate = Carbon::parse($ticket->withdraw_date);
            $correctDraw = $this->getDrawResultForDate($ticketDrawDate);

            if (!$correctDraw) {
                $transaction->status_info = ['status' => 'future', 'days_difference' => $ticketDrawDate->diffInDays(now())];
                $waitingForDraw->push($transaction);
                continue;
            }

            $compatibility = $this->checkDrawDateCompatibility($ticketDrawDate, Carbon::parse($correctDraw->draw_date));

            if ($compatibility['status'] === 'future') {
                $transaction->status_info = $compatibility;
                $waitingForDraw->push($transaction);
            } elseif ($compatibility['status'] === 'compatible') {
                $transaction->status_info = $compatibility;
                $transaction->ticket_numbers = $this->getTicketNumbers($ticket);
                $readyToCheck->push($transaction);
            } else {
                $transaction->status_info = $compatibility;
                $transaction->validation_error = 'Draw date too old';
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
        ];
    }

    /**
     * Get purchases grouped by draw status
     */
    public function getPurchasesByDrawStatus(): array
    {
        $latestDraw = $this->getLatestDrawResult();

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

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Check draw date compatibility (shared logic)
     */
    private function checkDrawDateCompatibility(Carbon $ticketDrawDate, Carbon $actualDrawDate): array
    {
        if ($actualDrawDate->lt($ticketDrawDate)) {
            $daysDiff = $ticketDrawDate->diffInDays($actualDrawDate);

            return [
                'status' => 'future',
                'days_difference' => $daysDiff,
                'expected_result_date' => $ticketDrawDate->format('M d, Y'),
            ];
        }

        $daysDiff = $actualDrawDate->diffInDays($ticketDrawDate);

        if ($daysDiff <= self::MAX_POSTPONE_DAYS) {
            return [
                'status' => 'compatible',
                'days_difference' => $daysDiff,
                'postponement' => $daysDiff > 0 ? "Draw postponed {$daysDiff} day(s)" : 'On time',
            ];
        }

        return [
            'status' => 'outdated',
            'days_difference' => $daysDiff,
        ];
    }

    /**
     * Extract ticket numbers from different formats
     */
    private function getTicketNumbers($ticket): array
    {
        $numbers = [];

        if (isset($ticket->numbers) && is_array($ticket->numbers)) {
            $numbers = $ticket->numbers;
        } elseif (isset($ticket->numbers) && is_string($ticket->numbers)) {
            $numbers = explode(',', $ticket->numbers);
        } elseif (isset($ticket->ticket_number)) {
            $numbers = [$ticket->ticket_number];
        }

        // Clean and validate each number (6-digit Thai lottery format)
        return array_filter(array_map(function ($num) {
            $cleaned = preg_replace('/[^0-9]/', '', trim($num));
            return strlen($cleaned) === 6 ? $cleaned : null;
        }, $numbers));
    }

    /**
     * Format win result with highest prize and all wins
     */
    private function formatWinResult(array $allWins): array
    {
        // Sort by reward amount (descending)
        usort($allWins, fn($a, $b) => ($b['reward'] ?? 0) <=> ($a['reward'] ?? 0));

        $highestPrize = $allWins[0];

        return [
            'prize' => $highestPrize['prize_name'] ?? 'Unknown Prize',
            'number' => $highestPrize['number'] ?? '',
            'all_wins' => $allWins,
            'total_prizes' => count($allWins),
            'highest_reward' => $highestPrize['reward'] ?? 0,
        ];
    }

    /**
     * Format prize info for display
     */
    private function formatPrizeInfo(array $winnerInfo): array
    {
        $prizeString = $winnerInfo['prize'] ?? 'Unknown Prize';
        $allWins = $winnerInfo['all_wins'] ?? [];

        $totalReward = array_sum(array_column($allWins, 'reward'));

        $displayPrize = $prizeString;
        if ($totalReward > 0) {
            $rewardFormatted = number_format($totalReward);
            $displayPrize .= " (Total: ฿{$rewardFormatted})";
        }

        return [
            'display_prize' => $displayPrize,
            'total_prizes' => $winnerInfo['total_prizes'] ?? 1,
            'highest_reward' => $winnerInfo['highest_reward'] ?? 0,
            'total_reward' => $totalReward,
        ];
    }

    /**
     * Perform recheck on a collection of transactions
     */
    private function performRecheck($transactions, DrawResult $drawResult): array
    {
        $changedCount = 0;
        $details = [
            'status_changed' => [],
            'prize_changed' => [],
        ];

        foreach ($transactions as $transaction) {
            $oldStatus = $transaction->status;
            $oldPrize = $transaction->prize_won;

            $newResult = $this->checkTransactionAgainstDraw($transaction, $drawResult);
            $newStatus = $newResult ? 'won' : 'not_won';
            $newPrize = $newResult ? $this->formatPrizeInfo($newResult)['display_prize'] : null;

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
                    ];
                    $changedCount++;
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

    // =========================================================================
    // RESPONSE BUILDERS
    // =========================================================================

    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'type' => 'error',
            'message' => "❌ {$message}",
            'checked' => 0,
            'won' => 0,
            'not_won' => 0,
            'skipped' => 0,
            'details' => [],
        ];
    }

    private function infoResponse(string $message, string $drawDate): array
    {
        return [
            'success' => true,
            'type' => 'info',
            'message' => "ℹ️ {$message}",
            'checked' => 0,
            'won' => 0,
            'not_won' => 0,
            'skipped' => 0,
            'draw_date' => $drawDate,
            'details' => [],
        ];
    }

    private function buildSuccessResponse(
        int $wonCount,
        int $notWonCount,
        int $skippedFuture,
        int $skippedOutdated,
        int $skippedNoTicket,
        string $drawDate,
        array $details,
        string $itemType
    ): array {
        $totalChecked = $wonCount + $notWonCount;
        $totalSkipped = $skippedFuture + $skippedOutdated + $skippedNoTicket;

        $message = $this->buildResultMessage(
            $totalChecked,
            $wonCount,
            $notWonCount,
            $skippedFuture,
            $skippedOutdated,
            $skippedNoTicket,
            $drawDate,
            $itemType
        );

        return [
            'success' => true,
            'type' => $totalChecked > 0 ? 'success' : 'warning',
            'message' => $message,
            'checked' => $totalChecked,
            'won' => $wonCount,
            'not_won' => $notWonCount,
            'skipped' => $totalSkipped,
            'skipped_future' => $skippedFuture,
            'skipped_outdated' => $skippedOutdated,
            'skipped_no_ticket' => $skippedNoTicket,
            'draw_date' => $drawDate,
            'details' => $details,
        ];
    }

    private function buildResultMessage(
        int $checked,
        int $won,
        int $notWon,
        int $future,
        int $outdated,
        int $noTicket,
        string $drawDate,
        string $itemType
    ): string {
        $parts = [];

        if ($checked > 0) {
            $parts[] = "✅ Checked <strong>{$checked}</strong> {$itemType}(s) against draw on <strong>{$drawDate}</strong>";

            if ($won > 0) {
                $parts[] = "🎉 <strong>{$won}</strong> winner(s)";
            }

            if ($notWon > 0) {
                $parts[] = "❌ <strong>{$notWon}</strong> not won";
            }
        }

        if ($future > 0) {
            $parts[] = "⏰ <strong>{$future}</strong> {$itemType}(s) waiting for future draw results";
        }

        if ($outdated > 0) {
            $parts[] = "⚠️ <strong>{$outdated}</strong> {$itemType}(s) have outdated draw dates";
        }

        if ($noTicket > 0) {
            $parts[] = "❓ <strong>{$noTicket}</strong> {$itemType}(s) have missing ticket information";
        }

        return implode('. ', $parts) . '.';
    }

    private function buildRecheckMessage(int $changed, int $unchanged, array $details, string $drawDate): string
    {
        $parts = [];

        $parts[] = "✅ Rechecked <strong>" . ($changed + $unchanged) . "</strong> transaction(s) against draw on <strong>{$drawDate}</strong>";

        if ($changed > 0) {
            $parts[] = "🔄 <strong>{$changed}</strong> transaction(s) had status changes";
        }

        if ($unchanged > 0) {
            $parts[] = "✅ <strong>{$unchanged}</strong> transaction(s) unchanged";
        }

        return implode('. ', $parts) . '.';
    }
}