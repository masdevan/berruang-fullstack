<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Services\WorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_workspace_generates_an_8_char_uppercase_code_and_adds_owner(): void
    {
        $me = User::factory()->create();

        $response = $this->actingAs($me)->postJson('/workspaces', ['name' => 'Tim Design'])
            ->assertOk();

        $workspace = Workspace::first();

        expect($workspace->name)->toBe('Tim Design');
        expect($workspace->owner_id)->toBe($me->id);
        expect(strlen($workspace->code))->toBe(8);
        expect(Str::upper($workspace->code))->toBe($workspace->code);
        expect(preg_match('/^[A-Z0-9]{8}$/', $workspace->code))->toBe(1);
        expect($workspace->members()->where('user_id', $me->id)->exists())->toBeTrue();
        expect($response->json('code'))->toBe($workspace->code);
        expect($response->json('html'))->toContain($workspace->code);
    }

    public function test_creating_a_workspace_requires_a_name(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->postJson('/workspaces', ['name' => ''])
            ->assertStatus(422);
    }

    public function test_joining_a_workspace_by_code_is_case_insensitive(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($joiner)->postJson('/workspaces/join', ['code' => strtolower($workspace->code)])
            ->assertOk();

        expect($joiner->workspaces()->where('workspace_id', $workspace->id)->exists())->toBeTrue();
    }

    public function test_joining_a_workspace_twice_is_rejected(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        $workspace->members()->attach($joiner->id);

        $this->actingAs($joiner)->postJson('/workspaces/join', ['code' => $workspace->code])
            ->assertStatus(422)
            ->assertJsonPath('message', 'You are already a member of this workspace.');
    }

    public function test_joining_an_unknown_workspace_is_rejected(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->postJson('/workspaces/join', ['code' => 'ZZZZ9999'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Workspace not found.');
    }

    public function test_workspace_code_must_be_8_alphanumeric_characters(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->postJson('/workspaces/join', ['code' => 'ABC'])
            ->assertStatus(422);

        $this->actingAs($me)->postJson('/workspaces/join', ['code' => 'ABC*12345'])
            ->assertStatus(422);
    }

    public function test_workspace_list_only_shows_joined_workspaces(): void
    {
        $owner = User::factory()->create();
        $me = User::factory()->create();
        $mine = app(WorkspaceService::class)->create($me, 'Milik Saya');
        $theirs = app(WorkspaceService::class)->create($owner, 'Punya Orang');

        $html = $this->actingAs($me)->get('/messages')->getContent();

        expect($html)->toContain('Milik Saya');
        expect($html)->toContain($mine->code);
        expect($html)->not->toContain('Punya Orang');
        expect($html)->not->toContain($theirs->code);
    }

    public function test_creator_is_owner_and_joiners_are_users(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        expect($workspace->members()->where('user_id', $owner->id)->first()->pivot->role)->toBe('owner');

        app(WorkspaceService::class)->join($joiner, $workspace->code);

        expect($workspace->members()->where('user_id', $joiner->id)->first()->pivot->role)->toBe('user');
    }

    public function test_members_endpoint_returns_owner_first_with_roles(): void
    {
        $owner = User::factory()->create(['name' => 'Budi Owner']);
        $joiner = User::factory()->create(['name' => 'Ayu Member']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($joiner, $workspace->code);

        $members = $this->actingAs($joiner)->getJson('/workspaces/'.$workspace->code.'/members')
            ->assertOk()
            ->json();

        expect($members)->toHaveCount(2);
        expect($members[0]['role'])->toBe('owner');
        expect($members[0]['name'])->toBe('Budi Owner');
        expect($members[1]['role'])->toBe('user');
        expect($members[1]['name'])->toBe('Ayu Member');
    }

    public function test_workspace_list_renders_my_role_server_side(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($joiner, $workspace->code);

        $ownerHtml = $this->actingAs($owner)->get('/messages')->getContent();
        expect($ownerHtml)->toContain('data-my-role="owner"');

        $joinerHtml = $this->actingAs($joiner)->get('/messages')->getContent();
        expect($joinerHtml)->toContain('data-my-role="user"');
    }

    public function test_owner_can_update_bio_and_code(): void
    {
        $owner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/configure', [
            'bio' => 'Tim pengembangan aplikasi',
            'code' => 'ABCDEF12',
        ])->assertOk()
            ->assertJsonPath('code', 'ABCDEF12')
            ->assertJsonPath('bio', 'Tim pengembangan aplikasi')
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'ABCDEF12'));

        expect($workspace->refresh()->code)->toBe('ABCDEF12');
        expect($workspace->bio)->toBe('Tim pengembangan aplikasi');
    }

    public function test_configure_rejects_taken_code(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->create($other, 'Tim Lain');

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/configure', [
            'code' => 'ABC1', // invalid format
        ])->assertStatus(422);

        $existing = Workspace::where('owner_id', $other->id)->first();

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/configure', [
            'code' => $existing->code,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'That code is already taken.');
    }

    public function test_regular_user_cannot_configure_workspace(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($joiner, $workspace->code);

        $this->actingAs($joiner)->postJson('/workspaces/'.$workspace->code.'/configure', [
            'bio' => 'ubah',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Only the owner or an admin can configure this workspace.');
    }

    public function test_owner_can_update_workspace_avatar(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/configure', [
            'avatar' => UploadedFile::fake()->image('logo.jpg', 200, 200),
        ])->assertOk()
            ->assertJsonPath('avatar', fn ($url) => is_string($url) && $url !== '');

        $workspace->refresh();

        expect($workspace->avatar)->not->toBeNull();
        Storage::disk('public')->assertExists($workspace->avatar);

        $preview = app(WorkspaceService::class)->avatarPreviewPath($workspace->avatar);
        expect(Storage::disk('public')->exists($preview))->toBeTrue();
        expect(Storage::disk('public')->size($preview))->toBeLessThanOrEqual(10 * 1024);
    }

    public function test_members_endpoint_rejects_non_members(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($stranger)->getJson('/workspaces/'.$workspace->code.'/members')
            ->assertStatus(404);
    }

    public function test_owner_is_included_in_members_list(): void
    {
        $owner = User::factory()->create(['name' => 'Boss Owner']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $members = $this->actingAs($owner)->getJson('/workspaces/'.$workspace->code.'/members')
            ->assertOk()
            ->json();

        expect($members)->toHaveCount(1);
        expect($members[0]['name'])->toBe('Boss Owner');
        expect($members[0]['role'])->toBe('owner');
    }
}
