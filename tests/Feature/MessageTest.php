<?php

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function contactPair(): array
{
    $user = User::factory()->create();
    $other = User::factory()->create();
    $user->contacts()->attach($other->id);
    $other->contacts()->attach($user->id);

    return [$user, $other];
}

test('a message can be sent to a contact', function () {
    [$user, $other] = contactPair();

    $this->actingAs($user)->post('/messages', [
        'to' => $other->username,
        'body' => 'Halo!',
    ])->assertOk()->assertJson(['id' => 1]);

    expect($other->receivedMessages()->count())->toBe(1);
    expect($other->receivedMessages()->first()->body)->toBe('Halo!');
});

test('message cannot be sent to a non-contact', function () {
    [$user] = contactPair();
    $stranger = User::factory()->create();

    $this->actingAs($user)->post('/messages', [
        'to' => $stranger->username,
        'body' => 'Halo!',
    ])->assertStatus(422);
});

test('thread returns messages from both sides with direction', function () {
    [$user, $other] = contactPair();

    $this->actingAs($user)->post('/messages', ['to' => $other->username, 'body' => 'dari user']);
    $this->actingAs($other)->post('/messages', ['to' => $user->username, 'body' => 'dari other']);

    $this->actingAs($user)->get('/messages/thread?with='.$other->username)
        ->assertOk()
        ->assertJsonCount(2, 'messages')
        ->assertJsonPath('messages.0.from', 'me')
        ->assertJsonPath('messages.1.from', 'other');
});

test('thread with after returns only newer messages', function () {
    [$user, $other] = contactPair();

    $this->actingAs($user)->post('/messages', ['to' => $other->username, 'body' => 'pertama']);
    $this->actingAs($other)->post('/messages', ['to' => $user->username, 'body' => 'kedua']);

    $this->actingAs($user)->get('/messages/thread?with='.$other->username.'&after=1')
        ->assertOk()
        ->assertJsonCount(1, 'messages')
        ->assertJsonPath('messages.0.body', 'kedua');
});

test('thread requires a contact or an existing thread', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $this->actingAs($user)->get('/messages/thread?with='.$stranger->username)
        ->assertStatus(422);
});

test('sending broadcasts MessageSent to the receiver channel', function () {
    [$user, $other] = contactPair();
    Event::fake([MessageSent::class]);

    $this->actingAs($user)->post('/messages', [
        'to' => $other->username,
        'body' => 'halo',
    ])->assertOk();

    Event::assertDispatched(MessageSent::class, function (MessageSent $e) use ($other) {
        return $e->message->receiver_id === $other->id;
    });
});

test('receiver who never saved the sender still gets the thread with real name and unsaved flag', function () {
    $user = User::factory()->create(['first_name' => 'Rizky', 'last_name' => 'Ramadhan']);
    $other = User::factory()->create();
    $user->contacts()->attach($other->id);

    $this->actingAs($user)->post('/messages', ['to' => $other->username, 'body' => 'halo']);

    expect($other->contacts()->where('contact_user_id', $user->id)->exists())->toBeTrue();

    $this->actingAs($other)->get('/messages/thread?with='.$user->username)
        ->assertOk()
        ->assertJsonCount(1, 'messages')
        ->assertJsonPath('messages.0.sender', 'Rizky Ramadhan')
        ->assertJsonPath('messages.0.custom', false);
});

test('saved contact name is used as sender with saved flag', function () {
    [$user, $other] = contactPair();
    $other->contacts()->updateExistingPivot($user->id, ['first_name' => 'Mas', 'last_name' => 'Devans']);

    $this->actingAs($user)->post('/messages', ['to' => $other->username, 'body' => 'halo']);

    $this->actingAs($other)->get('/messages/thread?with='.$user->username)
        ->assertOk()
        ->assertJsonPath('messages.0.sender', 'Mas Devans')
        ->assertJsonPath('messages.0.custom', true);
});

test('fetching the thread marks received messages as read', function () {
    [$user, $other] = contactPair();

    $this->actingAs($other)->post('/messages', ['to' => $user->username, 'body' => 'belum dibaca']);
    $this->actingAs($other)->post('/messages', ['to' => $user->username, 'body' => 'juga belum']);

    expect(Message::where('receiver_id', $user->id)->whereNull('read_at')->count())->toBe(2);

    $this->actingAs($user)->get('/messages/thread?with='.$other->username)->assertOk();

    expect(Message::where('receiver_id', $user->id)->whereNull('read_at')->count())->toBe(0);
});

test('sidebar shows last message, time and unread badge per contact', function () {
    [$user, $other] = contactPair();

    $this->actingAs($other)->post('/messages', ['to' => $user->username, 'body' => 'pesan kedua']);

    $this->actingAs($user)->get('/messages')
        ->assertOk()
        ->assertSee('pesan kedua', false)
        ->assertSee('unread-badge', false)
        ->assertSee('>1</span>', false);
});
