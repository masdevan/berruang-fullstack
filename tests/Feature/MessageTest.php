<?php

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

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

test('MessageSent broadcast payload includes file dimensions', function () {
    [$user, $other] = contactPair();

    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAIAAAAmkwkpAAAAFElEQVR4nGP8z8AARMAgYGRk+AcAHwEC/xL2XccAAAAASUVORK5CYII=');

    $this->actingAs($user)->post('/messages', [
        'to' => $other->username,
        'body' => 'foto',
        'file' => UploadedFile::fake()->createWithContent('foto.png', $png),
    ])->assertOk();

    $message = Message::first();

    $payload = (new MessageSent($message))->broadcastWith();

    expect($payload['type'])->toBe('image');
    expect($payload['file']['width'])->toBe(4);
    expect($payload['file']['height'])->toBe(4);
    expect($payload['sender_username'])->toBe($user->username);
});

test('MessageRead broadcast targets the sender channel with int ids and reader username', function () {
    [$user, $other] = contactPair();

    $this->actingAs($user)->post('/messages', ['to' => $other->username, 'body' => 'halo']);

    $this->actingAs($other)->get('/messages/thread?with='.$user->username)->assertOk();

    $message = Message::first();

    $event = new MessageRead($user, $other, [$message->id]);

    expect($event->broadcastOn()[0]->name)->toBe('private-App.Models.User.'.$user->id);
    expect($event->broadcastWith()['message_ids'])->toBe([$message->id]);
    expect($event->broadcastWith()['reader_username'])->toBe($other->username);
    expect($event->broadcastWith()['read_at'])->not->toBeNull();
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

test('marking a read via the read endpoint sets read_at and broadcasts', function () {
    [$user, $other] = contactPair();
    Event::fake([MessageRead::class]);

    $this->actingAs($user)->post('/messages', ['to' => $other->username, 'body' => 'halo']);

    $this->actingAs($other)->post('/messages/read', ['with' => $user->username])
        ->assertOk();

    expect(Message::where('receiver_id', $other->id)->whereNull('read_at')->count())->toBe(0);
    Event::assertDispatched(MessageRead::class, fn (MessageRead $e) => $e->reader->is($other));
});

test('read endpoint is a no-op without unread messages', function () {
    [$user, $other] = contactPair();
    Event::fake([MessageRead::class]);

    $this->actingAs($other)->post('/messages/read', ['with' => $user->username])
        ->assertOk();

    Event::assertNotDispatched(MessageRead::class);
});

test('sender thread shows read_at once the message was read', function () {
    [$user, $other] = contactPair();

    $this->actingAs($user)->post('/messages', ['to' => $other->username, 'body' => 'halo']);

    $this->actingAs($other)->get('/messages/thread?with='.$user->username)->assertOk();

    $this->actingAs($user)->get('/messages/thread?with='.$other->username)
        ->assertOk()
        ->assertJsonPath('messages.0.read_at', fn ($value) => $value !== null);
});

test('opening a thread broadcasts MessageRead to the sender channel', function () {
    [$user, $other] = contactPair();
    Event::fake([MessageRead::class]);

    $this->actingAs($user)->post('/messages', ['to' => $other->username, 'body' => 'halo']);

    $this->actingAs($other)->get('/messages/thread?with='.$user->username)->assertOk();

    Event::assertDispatched(MessageRead::class);
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

test('uploaded image stores its dimensions in the response', function () {
    [$user, $other] = contactPair();

    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAIAAAAmkwkpAAAAFElEQVR4nGP8z8AARMAgYGRk+AcAHwEC/xL2XccAAAAASUVORK5CYII=');

    $this->actingAs($user)->post('/messages', [
        'to' => $other->username,
        'body' => 'foto',
        'file' => UploadedFile::fake()->createWithContent('foto.png', $png),
    ])->assertOk()
        ->assertJsonPath('type', 'image')
        ->assertJsonPath('file.width', 4)
        ->assertJsonPath('file.height', 4);
});

test('large uploads keep the original and get a compressed preview under 10kb', function () {
    Storage::fake('public');
    [$user, $other] = contactPair();

    $img = imagecreatetruecolor(1600, 1200);
    for ($y = 0; $y < 1200; $y += 8) {
        for ($x = 0; $x < 1600; $x += 8) {
            imagesetpixel($img, $x, $y, imagecolorallocate($img, $x % 255, $y % 255, 128));
        }
    }
    $tmp = tempnam(sys_get_temp_dir(), 'brtest');
    imagepng($img, $tmp);
    imagedestroy($img);
    $png = file_get_contents($tmp);
    @unlink($tmp);

    $response = $this->actingAs($user)->post('/messages', [
        'to' => $other->username,
        'body' => 'foto besar',
        'file' => UploadedFile::fake()->createWithContent('besar.png', $png),
    ])->assertOk();

    $message = Message::first();

    expect($message->preview_path)->not->toBeNull();
    expect($response->json('file.preview_url'))->not->toBeNull();
    expect($response->json('file.width'))->toBe(1600);
    expect(Storage::disk('public')->exists($message->file_path))->toBeTrue();
    expect(Storage::disk('public')->exists($message->preview_path))->toBeTrue();
    expect(Storage::disk('public')->size($message->preview_path))->toBeLessThanOrEqual(10 * 1024);
});

test('uploaded svg stores its dimensions from width height or viewBox', function () {
    [$user, $other] = contactPair();

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480"><rect width="100%" height="100%"/></svg>';

    $this->actingAs($user)->post('/messages', [
        'to' => $other->username,
        'body' => 'vektor',
        'file' => UploadedFile::fake()->createWithContent('gambar.svg', $svg),
    ])->assertOk()
        ->assertJsonPath('type', 'image')
        ->assertJsonPath('file.width', 640)
        ->assertJsonPath('file.height', 480);
});
