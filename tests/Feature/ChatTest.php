<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
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

    public function test_chat_list_renders_offline_status_and_presence_channel_authorizes(): void
    {
        $me = User::factory()->create();
        $alice = User::factory()->create(['name' => 'Alice']);
        $bob = User::factory()->create(['name' => 'Bob']);
        $me->contacts()->attach([$alice->id, $bob->id]);

        $html = $this->actingAs($me)->get('/messages')->getContent();
        $this->assertStringContainsString('data-name="Alice" data-avatar="A" data-full-avatar="" data-has-avatar="0" data-status="offline"', $html);
        $this->assertStringContainsString('data-name="Bob" data-avatar="B" data-full-avatar="" data-has-avatar="0" data-status="offline"', $html);

        config(['broadcasting.default' => 'pusher']);
        config(['broadcasting.connections.pusher' => [
            'driver' => 'pusher',
            'key' => 'test-key',
            'secret' => 'test-secret',
            'app_id' => 'test-app',
            'options' => ['cluster' => 'mt1', 'useTLS' => false],
        ]]);
        Broadcast::channel('online', fn ($user) => [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
        ]);

        $response = $this->actingAs($alice)->postJson('/broadcasting/auth', [
            'channel_name' => 'presence-online',
            'socket_id' => '12345.6789',
        ]);

        $response->assertOk()
            ->assertJson(fn ($json) => $json->has('auth')
                ->where('channel_data', fn (string $data) => str_contains($data, '"username":"'.$alice->username.'"'))
                ->where('channel_data', fn (string $data) => str_contains($data, '"name":"Alice"'))
            );
    }

    public function test_thread_returns_last_25_messages_in_ascending_order_without_gaps(): void
    {
        $me = User::factory()->create();
        $alice = User::factory()->create();
        $me->contacts()->attach($alice->id);

        $ids = [];
        for ($i = 0; $i < 120; $i++) {
            $sender = $i % 2 === 0 ? $me : $alice;
            $ids[] = Message::create([
                'sender_id' => $sender->id,
                'receiver_id' => $sender->is($me) ? $alice->id : $me->id,
                'body' => "pesan-$i",
            ])->id;
        }

        $data = $this->actingAs($me)->getJson('/messages/thread?with='.$alice->username)
            ->assertOk()->json();

        $this->assertCount(25, $data['messages']);
        $this->assertTrue($data['has_more']);
        $this->assertSame(range(96, 120), array_column($data['messages'], 'id'));
        $this->assertSame('pesan-95', $data['messages'][0]['body']);
        $this->assertSame('pesan-119', $data['messages'][24]['body']);
    }

    public function test_thread_paginates_older_messages_with_before(): void
    {
        $me = User::factory()->create();
        $alice = User::factory()->create();
        $me->contacts()->attach($alice->id);

        for ($i = 0; $i < 120; $i++) {
            $sender = $i % 2 === 0 ? $me : $alice;
            Message::create([
                'sender_id' => $sender->id,
                'receiver_id' => $sender->is($me) ? $alice->id : $me->id,
                'body' => "pesan-$i",
            ]);
        }

        $before = 121;
        $seen = [];
        do {
            $data = $this->actingAs($me)->getJson('/messages/thread?with='.$alice->username.'&before='.$before)
                ->assertOk()->json();
            $seen = array_merge(array_column($data['messages'], 'id'), $seen);
            $before = $data['messages'][0]['id'] ?? 0;
        } while ($data['has_more']);

        $this->assertSame(range(1, 120), $seen);
    }

    public function test_conversation_preview_renders_read_check_when_last_message_was_sent_and_read(): void
    {
        $me = User::factory()->create();
        $alice = User::factory()->create(['name' => 'Alice']);
        $me->contacts()->attach($alice->id);
        $alice->contacts()->attach($me->id);

        Message::create([
            'sender_id' => $me->id,
            'receiver_id' => $alice->id,
            'body' => 'terkirim dan dibaca',
            'read_at' => now(),
        ]);

        $html = $this->actingAs($me)->get('/messages')->getContent();

        $this->assertStringContainsString('data-preview-owner="me"', $html);
        $this->assertStringContainsString('conversation-check', $html);
        $this->assertStringContainsString('text-[#E091A9]', $html);
    }

    public function test_conversation_preview_has_no_check_when_last_message_came_from_them(): void
    {
        $me = User::factory()->create();
        $alice = User::factory()->create(['name' => 'Alice']);
        $me->contacts()->attach($alice->id);

        Message::create([
            'sender_id' => $alice->id,
            'receiver_id' => $me->id,
            'body' => 'pesan dari alice',
        ]);

        $html = $this->actingAs($me)->get('/messages')->getContent();

        $this->assertStringContainsString('data-preview-owner="other"', $html);
        $this->assertStringNotContainsString('conversation-check', $html);
    }

    public function test_conversation_preview_shows_draft_label_over_last_message(): void
    {
        $me = User::factory()->create();
        $alice = User::factory()->create(['name' => 'Alice']);
        $me->contacts()->attach($alice->id);

        Message::create([
            'sender_id' => $alice->id,
            'receiver_id' => $me->id,
            'body' => 'pesan lama',
        ]);

        session(['chat_draft:'.$alice->username => 'draft aktif']);

        $html = $this->actingAs($me)->get('/messages')->getContent();

        $this->assertStringContainsString('Draft: draft aktif', $html);
        $this->assertStringNotContainsString('conversation-last text-[11px] truncate text-[#E091A9]/80">pesan lama', $html);
    }
}
