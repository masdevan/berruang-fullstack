<?php

namespace Tests\Feature;

use App\Events\TypingEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TypingTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_typing_broadcasts_to_receiver_channel(): void
    {
        Event::fake([TypingEvent::class]);
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me)->postJson('/typing', ['to' => $other->username, 'typing' => true])
            ->assertOk()
            ->assertJson(['ok' => true]);

        Event::assertDispatched(TypingEvent::class, function (TypingEvent $e) use ($me, $other) {
            return $e->toId === $other->id
                && $e->fromUsername === $me->username
                && $e->fromName === $me->name
                && $e->typing === true
                && $e->broadcastOn()->name === 'private-App.Models.User.'.$other->id;
        });
    }

    public function test_post_typing_unknown_user_returns_422(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->postJson('/typing', ['to' => 'ghost', 'typing' => false])
            ->assertStatus(422);
    }

    public function test_post_typing_requires_auth(): void
    {
        $this->postJson('/typing', ['to' => 'ghost', 'typing' => false])
            ->assertUnauthorized();
    }
}
