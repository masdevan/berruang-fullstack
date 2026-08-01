<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        return User::factory()->create();
    }

    public function test_add_user_by_username_creates_contact_and_returns_item_html(): void
    {
        $me = $this->actingUser();
        $target = User::factory()->create(['name' => 'Alice Wonder', 'username' => 'alice']);

        $response = $this->actingAs($me)->postJson('/contacts', ['username' => 'alice']);

        $response->assertOk()
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'data-name="Alice Wonder"'));
        $this->assertTrue($me->contacts()->where('contact_user_id', $target->id)->exists());
    }

    public function test_add_user_rejects_self_not_found_and_duplicates(): void
    {
        $me = $this->actingUser();
        $target = User::factory()->create(['username' => 'bob']);

        $this->actingAs($me)->postJson('/contacts', ['username' => $me->username])
            ->assertStatus(422);
        $this->actingAs($me)->postJson('/contacts', ['username' => 'ghost'])
            ->assertStatus(422);

        $this->actingAs($me)->postJson('/contacts', ['username' => 'bob'])->assertOk();
        $this->actingAs($me)->postJson('/contacts', ['username' => 'bob'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'This user is already in your contacts.');
    }

    public function test_contacts_index_paginates_and_renders_items(): void
    {
        $me = $this->actingUser();
        $contacts = User::factory()->count(3)->create(['name' => 'Zoe Contact']);
        $me->contacts()->attach($contacts->pluck('id'));

        $first = $this->actingAs($me)->getJson('/contacts?page=1')->assertOk()->json();
        $this->assertStringContainsString('data-name="Zoe Contact"', $first['html']);

        $second = $this->actingAs($me)->getJson('/contacts?page=2')->assertOk()->json();
        $this->assertFalse($second['has_more']);
    }

    public function test_online_flag_renders_in_item_html(): void
    {
        $me = $this->actingUser();
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

        $html = $this->actingAs($me)->getJson('/contacts?page=1')->json('html');
        $this->assertStringContainsString('data-name="Alice" data-avatar="A" data-online="1"', $html);
        $this->assertStringContainsString('data-name="Bob" data-avatar="B" data-online="0"', $html);
    }

    public function test_update_names_sets_custom_contact_name_in_html(): void
    {
        $me = $this->actingUser();
        $target = User::factory()->create(['name' => 'Alice Wonder', 'username' => 'alice']);
        $me->contacts()->attach($target->id);

        $response = $this->actingAs($me)->patchJson("/contacts/{$target->id}", [
            'first_name' => 'Ayu',
            'last_name' => 'Lestari',
        ]);

        $response->assertOk()
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'data-name="Ayu Lestari"'));
        $this->assertSame('Ayu', DB::table('contacts')->where('contact_user_id', $target->id)->value('first_name'));
    }
}
