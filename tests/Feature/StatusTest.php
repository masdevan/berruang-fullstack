<?php

namespace Tests\Feature;

use App\Events\UserStatusChanged;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_status_caches_and_broadcasts(): void
    {
        Event::fake([UserStatusChanged::class]);
        $me = User::factory()->create();

        $this->actingAs($me)->postJson('/presence-status', ['status' => 'idle'])
            ->assertOk();

        $this->assertSame('idle', Cache::get('presence.status.'.$me->id));
        Event::assertDispatched(UserStatusChanged::class, fn ($event) => $event->username === $me->username && $event->status === 'idle');
    }

    public function test_post_status_rejects_invalid_status(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->postJson('/presence-status', ['status' => 'banana'])
            ->assertStatus(422);
    }

    public function test_get_statuses_returns_cached_map_for_existing_users(): void
    {
        $me = User::factory()->create();
        $alice = User::factory()->create();
        Cache::put('presence.status.'.$alice->id, 'idle', now()->addMinutes(2));

        $this->actingAs($me)->getJson('/presence-status?users=ghost,'.$alice->username)
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath($alice->username, 'idle');
    }
}
