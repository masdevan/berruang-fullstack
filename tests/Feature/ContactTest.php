<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_contacts_options_returns_contacts_json(): void
    {
        $me = $this->actingUser();
        $a = User::factory()->create(['name' => 'Alice Wonder', 'username' => 'alice']);
        $b = User::factory()->create(['name' => 'Budi Santoso', 'username' => 'budi']);

        $me->contacts()->attach([$a->id, $b->id]);

        $options = $this->actingAs($me)->getJson('/contacts/options')
            ->assertOk()
            ->json();

        expect($options)->toHaveCount(2);
        expect($options[0]['username'])->toBe('alice');
        expect($options[0]['name'])->toBe('Alice Wonder');
        expect($options[0]['has_avatar'])->toBeFalse();
        expect($options[1]['username'])->toBe('budi');
    }

    public function test_contacts_options_only_returns_own_contacts(): void
    {
        $me = $this->actingUser();
        $stranger = User::factory()->create(['username' => 'orang']);
        $stranger2 = User::factory()->create(['username' => 'orang2']);

        $stranger->contacts()->attach($stranger2->id);

        $options = $this->actingAs($me)->getJson('/contacts/options')
            ->assertOk()
            ->json();

        expect($options)->toHaveCount(0);
    }

    public function test_add_user_accepts_at_prefix_and_check_username(): void
    {
        $me = $this->actingUser();
        $target = User::factory()->create(['username' => 'alice']);

        $this->actingAs($me)->postJson('/contacts', ['username' => '@alice'])
            ->assertOk();

        $this->assertTrue($me->contacts()->where('contact_user_id', $target->id)->exists());

        $this->getJson('/check-username/@alice')->assertJsonPath('taken', true);
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
