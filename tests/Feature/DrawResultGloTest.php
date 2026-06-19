<?php

namespace Tests\Feature;

use App\Http\Controllers\DrawResultController;
use Tests\TestCase;

class DrawResultGloTest extends TestCase
{
    public function test_glo_api_mapping_works_correctly()
    {
        $controller = new DrawResultController();
        
        // Mock GLO API Response Data
        $mockGloData = [
            'statusMessage' => 'Success',
            'statusCode' => 200,
            'response' => [
                'date' => '2026-06-16',
                'data' => [
                    'first' => [
                        'price' => '6000000.00',
                        'number' => [['round' => 1, 'value' => '287184']]
                    ],
                    'near1' => [
                        'price' => '100000.00',
                        'number' => [
                            ['round' => 1, 'value' => '287183'],
                            ['round' => 2, 'value' => '287185']
                        ]
                    ],
                    'second' => [
                        'price' => '200000.00',
                        'number' => [
                            ['round' => 5, 'value' => '124998'],
                            ['round' => 2, 'value' => '281342']
                        ]
                    ],
                    'last2' => [
                        'price' => '2000.00',
                        'number' => [['round' => 1, 'value' => '48']]
                    ],
                    'last3f' => [
                        'price' => '4000.00',
                        'number' => [
                            ['round' => 2, 'value' => '434'],
                            ['round' => 1, 'value' => '758']
                        ]
                    ],
                    'last3b' => [
                        'price' => '4000.00',
                        'number' => [
                            ['round' => 1, 'value' => '007'],
                            ['round' => 2, 'value' => '721']
                        ]
                    ]
                ]
            ]
        ];

        // Access the private mapGloApiData method via reflection
        $reflection = new \ReflectionClass(DrawResultController::class);
        $method = $reflection->getMethod('mapGloApiData');
        $method->setAccessible(true);

        $result = $method->invokeArgs($controller, [$mockGloData]);

        // Assertions
        $this->assertNotNull($result);
        $this->assertEquals('2026-06-16', $result['draw_date']);
        $this->assertEquals('16 มิถุนายน 2569', $result['date_th']);
        $this->assertEquals('16 June 2026', $result['date_en']);
        
        // Assert prizes
        $prizes = $result['prizes'];
        $this->assertCount(3, $prizes); // first, near1, second
        
        // Assert prizeFirst
        $firstPrize = collect($prizes)->firstWhere('id', 'prizeFirst');
        $this->assertNotNull($firstPrize);
        $this->assertEquals('รางวัลที่ 1', $firstPrize['name']);
        $this->assertEquals(6000000, $firstPrize['reward']);
        $this->assertEquals(1, $firstPrize['amount']);
        $this->assertEquals(['287184'], $firstPrize['number']);

        // Assert prizeFirstNear
        $nearPrize = collect($prizes)->firstWhere('id', 'prizeFirstNear');
        $this->assertNotNull($nearPrize);
        $this->assertEquals('รางวัลข้างเคียงรางวัลที่ 1', $nearPrize['name']);
        $this->assertEquals(100000, $nearPrize['reward']);
        $this->assertEquals(2, $nearPrize['amount']);
        $this->assertEquals(['287183', '287185'], $nearPrize['number']);

        // Assert running numbers
        $running = $result['running_numbers'];
        $this->assertCount(3, $running); // last2, last3f, last3b
        
        // Assert runningNumberBackTwo
        $backTwo = collect($running)->firstWhere('id', 'runningNumberBackTwo');
        $this->assertNotNull($backTwo);
        $this->assertEquals('รางวัลเลขท้าย 2 ตัว', $backTwo['name']);
        $this->assertEquals(2000, $backTwo['reward']);
        $this->assertEquals(1, $backTwo['amount']);
        $this->assertEquals(['48'], $backTwo['number']);

        // Assert runningNumberFrontThree
        $frontThree = collect($running)->firstWhere('id', 'runningNumberFrontThree');
        $this->assertNotNull($frontThree);
        $this->assertEquals('รางวัลเลขหน้า 3 ตัว', $frontThree['name']);
        $this->assertEquals(4000, $frontThree['reward']);
        $this->assertEquals(2, $frontThree['amount']);
        $this->assertEquals(['434', '758'], $frontThree['number']);
    }
}
