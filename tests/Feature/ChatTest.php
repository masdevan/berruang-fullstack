<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_list_shows_only_contacts_and_hides_self(): void
    {
        $me = User::factory()->create(['name' => 'Me Person']);
        $alice = User::factory()->create(['name' => 'Alice Wonder']);
        $bob = User::factory()->create(['name' => 'Bob Builder']);
        $me->contacts()->attach($alice->id);

        $this->actingAs($me)->get('/messages')
            ->assertOk()
            ->assertSee('Alice Wonder')
            ->assertSee('add-user-status')
            ->assertDontSee('Bob Builder')
            ->assertDontSee('Me Person');

        $this->actingAs($me)->get('/profile')
            ->assertOk()
            ->assertSee('Alice Wonder')
            ->assertDontSee('Bob Builder');
    }

    public function test_online_status_marks_users_with_active_session(): void
    {
        $me = User::factory()->create();
        $alice = User::factory()->create(['name' => 'Alice']);
        $bob = User::factory()->create(['name' => 'Bob']);
        $me->contacts()->attach([$alice->id, $bob->id]);

        DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => $alice->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'test',
            'last_activity' => now()->timestamp,
        ]);

        $html = $this->actingAs($me)->get('/messages')->getContent();
        $this->assertStringContainsString('data-name="Alice" data-avatar="A" data-online="1"', $html);
        $this->assertStringContainsString('data-name="Bob" data-avatar="B" data-online="0"', $html);
    }
}
