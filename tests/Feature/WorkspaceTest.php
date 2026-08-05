<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Services\WorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
