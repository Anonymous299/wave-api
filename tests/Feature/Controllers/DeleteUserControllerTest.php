<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_user()
    {
        $user = User::factory()->create();
        $bio = $user->bio()->create();

        $this->actingAs($user)->delete(route('users.delete'))->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $user->getKey()]);
        $this->assertDatabaseMissing('bios', ['id' => $bio->getKey()]);
    }

    public function test_it_deletes_user_images_from_real_storage()
    {
        Storage::fake();

        $imagePaths = [
            'uploads/image1.jpg',
            'uploads/image2.jpg',
        ];

        foreach ($imagePaths as $path) {
            Storage::put($path, 'fake content');
            Storage::assertExists($path);
        }

        $user = User::factory()->create();

        $user->bio()->create([
            'images' => array_map(fn($path) => "https://example-bucket.s3.amazonaws.com/{$path}", $imagePaths),
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson(route('users.delete'));

        foreach ($imagePaths as $path) {
            Storage::assertMissing($path);
        }

        $response->assertNoContent();
    }

}
