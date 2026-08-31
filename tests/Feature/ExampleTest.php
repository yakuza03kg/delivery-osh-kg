<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_guest_can_open_the_delivery_calculator(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Рассчитать доставку');
    }
}
