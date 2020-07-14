<?php

namespace Tests\Feature\Http\Controllers;

use App\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\TripController
 */
class TripControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function show_behaves_as_expected()
    {
        $trip = factory(Trip::class)->create();

        $response = $this->get(route('trip.show', $trip));
    }


    /**
     * @test
     */
    public function search_behaves_as_expected()
    {
        $trip = factory(Trip::class)->create();

        $response = $this->get(route('trip.search'));
    }


    /**
     * @test
     */
    public function search_id_behaves_as_expected()
    {
        $trip = factory(Trip::class)->create();

        $response = $this->get(route('trip.search_id'));
    }
}
