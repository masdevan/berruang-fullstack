<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SetupProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_saves_bio_avatar_and_marks_onboarded(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/setup-profile', [
            'bio' => 'Hello world',
            'avatar' => UploadedFile::fake()->image('me.jpg'),
        ]);

        $response->assertRedirect('/messages');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'bio' => 'Hello world',
        ]);
        $this->assertNotNull($user->fresh()->onboarded_at);
        $this->assertNotNull($user->fresh()->avatar);
    }

    public function test_skip_marks_onboarded_without_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/setup-profile', ['skip' => '1']);

        $response->assertRedirect('/messages');
        $this->assertNotNull($user->fresh()->onboarded_at);
    }
}
