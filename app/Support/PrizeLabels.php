<?php

namespace App\Support;

/**
 * Prize naming and ordering for draw results.
 *
 * The results API stores prize categories under their Thai names. Both the
 * admin draw-result screens and the public breakdown page need the same
 * English labels in the same order, so the mapping lives here rather than
 * being repeated per controller.
 */
final class PrizeLabels
{
    private const NAMES = [
        'รางวัลที่ 1' => 'First Prize',
        'รางวัลข้างเคียงรางวัลที่ 1' => '1st Prize Neighbor',
        'รางวัลที่ 2' => 'Second Prize',
        'รางวัลที่ 3' => 'Third Prize',
        'รางวัลที่ 4' => 'Fourth Prize',
        'รางวัลที่ 5' => 'Fifth Prize',
        'รางวัลเลขหน้า 3 ตัว' => 'Front Three Digits',
        'รางวัลเลขท้าย 3 ตัว' => 'Back Three Digits',
        'รางวัลเลขท้าย 2 ตัว' => 'Back Two Digits',
    ];

    private const ORDER = [
        'First Prize' => 1,
        '1st Prize Neighbor' => 2,
        'Front Three Digits' => 3,
        'Back Three Digits' => 4,
        'Second Prize' => 5,
        'Third Prize' => 6,
        'Fourth Prize' => 7,
        'Fifth Prize' => 8,
        'Back Two Digits' => 9,
    ];

    public static function english(?string $name): string
    {
        return self::NAMES[$name] ?? (string) $name;
    }

    /** Normalise a raw prizes/running_numbers array: English names, prize order, list of numbers. */
    public static function normalise(mixed $data): array
    {
        $items = is_string($data) ? json_decode($data, true) : $data;
        if (! is_array($items)) {
            return [];
        }

        $items = array_map(function ($item) {
            $item['name'] = self::english($item['name'] ?? null);
            $item['order'] = self::ORDER[$item['name']] ?? 999;
            $item['number'] = array_values((array) ($item['number'] ?? []));

            return $item;
        }, $items);

        usort($items, fn($a, $b) => $a['order'] <=> $b['order']);

        return $items;
    }

    /** Total count of individual winning numbers across the given categories. */
    public static function countNumbers(array $items): int
    {
        return array_sum(array_map(fn($i) => count((array) ($i['number'] ?? [])), $items));
    }
}
