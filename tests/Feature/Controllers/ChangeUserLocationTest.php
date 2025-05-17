<?php

namespace Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeUserLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_changes_location()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('users.location.update'), [
                'latitude'  => 52.5200,
                'longitude' => 13.4050,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'location' => Point::makeGeodetic(52.5200, 13.4050),
        ]);
    }
}
