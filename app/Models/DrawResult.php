<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrawResult extends Model
{
    protected $fillable = [
        'draw_date', 'date_th', 'date_en', 'prizes', 'running_numbers', 'endpoint'
    ];
    protected $casts = [
        'prizes' => 'array',
        'running_numbers' => 'array',
        'draw_date' => 'date',
    ];
    public function getNormalizedPrizesAttribute()
    {
        $prizes = $this->prizes;
        
        if (!is_array($prizes)) {
            return [];
        }

        // Check if it's already in the expected format
        if (isset($prizes['first_prize']) || isset($prizes['prizeFirst'])) {
            return $this->convertToExpectedFormat($prizes);
        }

        // Convert from API format to expected format
        $normalized = [];
        
        foreach ($prizes as $prize) {
            if (!is_array($prize) || !isset($prize['id']) || !isset($prize['number'])) {
                continue;
            }

            $key = $this->mapPrizeId($prize['id']);
            $normalized[$key] = $prize['number'];
        }

        return $normalized;
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
        
        foreach ($prizes as $key => $value) {
            if (strpos($key, 'prize') === 0) {
                $normalized[$this->mapPrizeId($key)] = $value;
            } else {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }
}
