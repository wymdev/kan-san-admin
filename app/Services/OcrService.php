<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OcrService
{
    private string $openaiApiKey;
    private string $model = 'gpt-4o';

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key', env('OPENAI_API_KEY', ''));
    }

    /**
     * Extract lottery numbers from an uploaded image using OpenAI Vision
     * Falls back to Tesseract if OpenAI is not configured
     */
    public function extractNumbersFromImage(string $imagePath): array
    {
        $fullPath = Storage::disk('public')->path($imagePath);
        
        if (!file_exists($fullPath)) {
            return [
                'success' => false,
                'error' => 'Image file not found',
                'numbers' => [],
            ];
        }

        // Try OpenAI Vision first (best accuracy)
        if (!empty($this->openaiApiKey)) {
            $result = $this->extractWithOpenAI($fullPath);
            if ($result['success']) {
                return $result;
            }
            // Log the error but continue to fallback
            Log::warning('OpenAI OCR failed, falling back to Tesseract: ' . ($result['error'] ?? 'Unknown error'));
        }

        // Fallback to Tesseract
        return $this->extractWithTesseract($fullPath);
    }

    /**
     * Extract numbers using OpenAI Vision API (GPT-4o)
     */
    private function extractWithOpenAI(string $imagePath): array
    {
        try {
            // Convert image to base64
            $imageData = file_get_contents($imagePath);
            $base64Image = base64_encode($imageData);
            $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

            // Prepare the prompt
            $prompt = "Look at this image of Thai lottery tickets. Extract ONLY the 6-digit lottery numbers from each ticket. The numbers are typically printed in large font on the right side of each ticket.

Rules:
1. Only extract 6-digit numbers (the main lottery numbers)
2. Ignore serial numbers, dates, and other text
3. Return ONLY the numbers, one per line
4. No explanations, no formatting, just the numbers

Example output format:
698217
943015
703913";

            // Call OpenAI Vision API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt,
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$base64Image}",
                                    'detail' => 'high',
                                ],
                            ],
                        ],
                    ],
                ],
                'max_tokens' => 500,
                'temperature' => 0,
            ]);

            if ($response->failed()) {
                $error = $response->json('error.message') ?? $response->body();
                Log::error('OpenAI API Error: ' . $error);
                return [
                    'success' => false,
                    'error' => 'OpenAI API error: ' . $error,
                    'numbers' => [],
                ];
            }

            $content = $response->json('choices.0.message.content', '');
            
            // Extract 6-digit numbers from the response
            $numbers = $this->extractNumbersFromText($content);

            return [
                'success' => true,
                'raw_text' => $content,
                'numbers' => $numbers,
                'count' => count($numbers),
                'source' => 'openai',
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI Vision OCR Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'numbers' => [],
            ];
        }
    }

    /**
     * Fallback: Extract numbers using Tesseract OCR
     */
    private function extractWithTesseract(string $imagePath): array
    {
        try {
            if (!$this->isTesseractAvailable()) {
                return [
                    'success' => false,
                    'error' => 'Tesseract OCR is not installed and OpenAI API key is not configured.',
                    'numbers' => [],
                ];
            }

            $allText = '';
            
            // Run multiple OCR strategies
            $cmd1 = sprintf(
                'tesseract %s stdout --psm 6 -c tessedit_char_whitelist=0123456789 2>/dev/null',
                escapeshellarg($imagePath)
            );
            exec($cmd1, $out1);
            $allText .= implode("\n", $out1) . "\n";

            $cmd2 = sprintf(
                'tesseract %s stdout --psm 4 -c tessedit_char_whitelist=0123456789 2>/dev/null',
                escapeshellarg($imagePath)
            );
            $out2 = [];
            exec($cmd2, $out2);
            $allText .= implode("\n", $out2) . "\n";

            $numbers = $this->extractNumbersFromText($allText);

            return [
                'success' => true,
                'raw_text' => $allText,
                'numbers' => $numbers,
                'count' => count($numbers),
                'source' => 'tesseract',
            ];

        } catch (\Exception $e) {
            Log::error('Tesseract OCR Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'numbers' => [],
            ];
        }
    }

    /**
     * Extract 6-digit numbers from text
     */
    private function extractNumbersFromText(string $text): array
    {
        $numbers = [];
        
        // Find all 6-digit numbers
        preg_match_all('/\b(\d{6})\b/', $text, $matches);
        if (!empty($matches[1])) {
            $numbers = array_merge($numbers, $matches[1]);
        }

        // Also check lines with spaces between digits
        preg_match_all('/(\d\s*\d\s*\d\s*\d\s*\d\s*\d)/', $text, $spacedMatches);
        if (!empty($spacedMatches[1])) {
            foreach ($spacedMatches[1] as $spacedNum) {
                $cleanNum = preg_replace('/\s+/', '', $spacedNum);
                if (strlen($cleanNum) === 6) {
                    $numbers[] = $cleanNum;
                }
            }
        }

        // Deduplicate, filter, and sort
        $numbers = array_filter(array_unique($numbers), fn($n) => preg_match('/^\d{6}$/', $n));
        $numbers = array_values($numbers);
        sort($numbers);

        return $numbers;
    }

    /**
     * Check if Tesseract is available
     */
    private function isTesseractAvailable(): bool
    {
        exec('which tesseract 2>&1', $output, $retval);
        return $retval === 0;
    }

    /**
     * Utility: Convert number to digit array
     */
    public function numberToDigitArray(string $number): array
    {
        return str_split(preg_replace('/\D/', '', $number));
    }

    /**
     * Utility: Validate lottery number
     */
    public function isValidLotteryNumber(string $number): bool
    {
        return strlen(preg_replace('/\D/', '', $number)) === 6;
    }
}
