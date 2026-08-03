<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_is_saved_per_contact(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post('/chat/draft?to=alice&text=Halo+draft')
            ->assertNoContent();

        $this->assertSame('Halo draft', session('chat_draft:alice'));
    }

    public function test_empty_draft_clears_the_stored_value(): void
    {
        $me = User::factory()->create();
        session(['chat_draft:alice' => 'Halo draft']);

        $this->actingAs($me)->post('/chat/draft?to=alice&text=')
            ->assertNoContent();

        $this->assertNull(session('chat_draft:alice'));
    }

    public function test_draft_without_to_is_ignored(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post('/chat/draft?text=Halo')
            ->assertNoContent();

        $this->assertFalse(session()->has('chat_draft:'));
    }

    public function test_draft_endpoint_requires_auth(): void
    {
        $this->post('/chat/draft?to=alice&text=Halo')
            ->assertRedirect('/login');
    }
}
