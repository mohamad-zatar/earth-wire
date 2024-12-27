<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_preferences()
    {
        User::factory()->create();

        $payload = [
            'sources' => ['BBC', 'CNN'],
            'categories' => ['technology', 'health'],
            'authors' => ['John Doe', 'Jane Smith'],
        ];

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/preferences', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Preferences saved successfully.',
            ]);
    }
}
