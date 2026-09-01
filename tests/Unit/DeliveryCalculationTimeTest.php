<?php

namespace Tests\Unit;

use App\Models\DeliveryCalculation;
use Carbon\Carbon;
use Tests\TestCase;

class DeliveryCalculationTimeTest extends TestCase
{
    public function test_calculation_time_is_formatted_for_bishkek(): void
    {
        config()->set('delivery.timezone', 'Asia/Bishkek');

        $calculation = new DeliveryCalculation();
        $calculation->created_at = Carbon::create(2026, 9, 1, 8, 19, 0, 'UTC');

        $this->assertSame('01.09.2026 14:19', $calculation->formattedCreatedAt());
    }
}
