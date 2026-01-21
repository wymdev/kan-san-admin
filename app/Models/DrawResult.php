<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrawResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'draw_date',
        'date_th',
        'date_en',
        'prizes',
        'running_numbers',
        'endpoint',
        'prizes_th', // JSON (Thai labels if needed)
        'prizes_en', // JSON (English labels if needed)
    ];

    protected $casts = [
        'draw_date' => 'date',
        'prizes' => 'array',
        'running_numbers' => 'array',
        'prizes_th' => 'array',
        'prizes_en' => 'array',
    ];

    /**
     * Get normalized prizes (keyed by standard keys)
     */
    public function getNormalizedPrizesAttribute()
    {
        $prizes = $this->prizes;

        if (!$prizes) {
            return [];
        }

        // Keep simplified
        return $this->convertToExpectedFormat($prizes);
    }

    /**
     * Scope: Find draw result for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('draw_date', $date);
    }

    /**
     * Check if a number is a winner - returns ALL matching prizes
     */
    public function checkNumber($number)
    {
        $ticketNumber = str_replace(' ', '', (string) $number);

        // Pad with leading zeros to ensure 6 digits (Thai lottery standard)
        $ticketNumber = str_pad($ticketNumber, 6, '0', STR_PAD_LEFT);

        $allWins = []; // Collect ALL matching prizes

        // 1. Check Standard Prizes (First, Second, etc.) - exact 6-digit match
        $prizes = $this->normalized_prizes;
        if (is_array($prizes)) {
            foreach ($prizes as $prizeKey => $winningNumbers) {
                if (!is_array($winningNumbers)) {
                    $winningNumbers = [$winningNumbers];
                }
                foreach ($winningNumbers as $winningNumber) {
                    $winningNumber = str_replace(' ', '', $winningNumber);
                    if ($ticketNumber === $winningNumber) {
                        $allWins[] = [
                            'won' => true,
                            'prize_id' => $prizeKey,
                            'prize_name' => $this->getPrizeLabel($prizeKey),
                            'number' => $ticketNumber,
                            'full_number' => $ticketNumber,
                            'reward' => $this->getRewardAmount($prizeKey),
                        ];
                    }
                }
            }
        }

        // 2. Check Running Numbers (Front 3, Rear 3, Rear 2) - partial match
        $runningNumbers = $this->running_numbers;

        if (is_array($runningNumbers)) {
            foreach ($runningNumbers as $running) {
                $winningNumbers = [];
                $runKey = 'running';
                $prizeName = 'Running Number';

                if (isset($running['id']) && isset($running['number'])) {
                    $runKey = $running['id'];
                    $winningNumbers = $running['number'];
                    $prizeName = $this->getPrizeLabel($runKey);
                } elseif (is_array($running)) {
                    $winningNumbers = $running;
                } else {
                    continue;
                }

                if (!is_array($winningNumbers)) {
                    $winningNumbers = [$winningNumbers];
                }

                foreach ($winningNumbers as $winningNumber) {
                    $winningNumber = str_replace(' ', '', $winningNumber);
                    $len = strlen($winningNumber);

                    if ($len > 0 && strlen($ticketNumber) >= $len) {
                        $isMatch = false;

                        // Check logic based on key
                        if (stripos($runKey, 'Front') !== false || stripos($runKey, 'front') !== false) {
                            // Front Match - compare first N digits
                            if (substr($ticketNumber, 0, $len) === $winningNumber) {
                                $isMatch = true;
                            }
                        } else {
                            // Rear Match (Default) - compare last N digits
                            if (substr($ticketNumber, -$len) === $winningNumber) {
                                $isMatch = true;
                            }
                        }

                        if ($isMatch) {
                            $reward = $this->getRewardAmount($runKey);

                            // Fallback if 0 (Key mismatch protection)
                            if ($reward == 0) {
                                if (stripos($prizeName, 'Front 3') !== false || stripos($prizeName, 'Rear 3') !== false) {
                                    $reward = 4000;
                                } elseif (stripos($prizeName, '2 Digit') !== false) {
                                    $reward = 2000;
                                } elseif (stripos($prizeName, '1st Prize') !== false) {
                                    $reward = 6000000;
                                }
                            }

                            $allWins[] = [
                                'won' => true,
                                'prize_id' => $runKey,
                                'prize_name' => $prizeName,
                                'number' => $winningNumber,
                                'full_number' => $ticketNumber,
                                'reward' => $reward,
                            ];

                            // Don't break - continue checking for more matches in same category
                            // But avoid duplicate entries for same prize type
                            break;
                        }
                    }
                }
            }
        }

        // Return all wins or false if none
        if (count($allWins) > 0) {
            return $allWins;
        }

        return false;
    }

    /**
     * Helper to get English Prize Label
     */
    public function getPrizeLabel($key)
    {
        $labels = [
            'first_prize' => '1st Prize',
            'prize_1' => '1st Prize',
            'near_first_prize' => 'Near 1st Prize',
            'second_prize' => '2nd Prize',
            'prize_2' => '2nd Prize',
            'third_prize' => '3rd Prize',
            'prize_3' => '3rd Prize',
            'fourth_prize' => '4th Prize',
            'prize_4' => '4th Prize',
            'fifth_prize' => '5th Prize',
            'prize_5' => '5th Prize',
            'runningNumberFrontThree' => 'Front 3 Digits',
            'runningNumberBackThree' => 'Rear 3 Digits',
            'runningNumberBackTwo' => '2 Digit Suffix',
            'running_3_front' => 'Front 3 Digits',
            'running_3_back' => 'Rear 3 Digits',
            'running_2_back' => '2 Digit Suffix',
        ];

        return $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Helper to get Reward Amount (Approximation)
     */
    public function getRewardAmount($key)
    {
        $rewards = [
            'first_prize' => 6000000,
            'prize_1' => 6000000,
            'near_first_prize' => 100000,
            'second_prize' => 200000,
            'prize_2' => 200000,
            'third_prize' => 80000,
            'prize_3' => 80000,
            'fourth_prize' => 40000,
            'prize_4' => 40000,
            'fifth_prize' => 20000,
            'prize_5' => 20000,
            'runningNumberFrontThree' => 4000,
            'runningNumberBackThree' => 4000,
            'runningNumberBackTwo' => 2000,
            'running_3_front' => 4000,
            'running_3_back' => 4000,
            'running_2_back' => 2000,
        ];

        return $rewards[$key] ?? 0;
    }

    /**
     * Map API prize IDs to internal prize keys
     */
    private function mapPrizeId($id)
    {
        $mapping = [
            'prizeFirst' => 'first_prize',
            'prizeFirstNear' => 'near_first_prize',
            'prizeSecond' => 'second_prize',
            'prizeThird' => 'third_prize',
            'prizeForth' => 'fourth_prize',
            'prizeFifth' => 'fifth_prize',
        ];

        return $mapping[$id] ?? $id;
    }

    /**
     * Convert to expected format if needed
     */
    private function convertToExpectedFormat($prizes)
    {
        $normalized = [];

        foreach ($prizes as $prize) {
            // Check if it's the new object format
            if (is_array($prize) && isset($prize['id']) && isset($prize['number'])) {
                $key = $this->mapPrizeId($prize['id']);
                $normalized[$key] = $prize['number'];
            }
            // Handle legacy format if any (key => value) - unlikely with current data but safe to keep checks if needed
            // For now, we assume the structure seen in tinker
        }

        return $normalized;
    }
}
