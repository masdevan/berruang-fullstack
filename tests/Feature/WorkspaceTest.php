<?php

namespace Tests\Feature;

use App\Events\WorkspaceMembersChanged;
use App\Events\WorkspaceMessageSent;
use App\Events\WorkspaceMessagesRead;
use App\Events\WorkspaceTyping;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMessage;
use App\Services\WorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
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

    public function test_creating_a_workspace_with_custom_code(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->postJson('/workspaces', ['name' => 'Tim Dev', 'code' => 'ABCDEF12'])
            ->assertOk()
            ->assertJsonPath('code', 'ABCDEF12');

        expect(Workspace::where('code', 'ABCDEF12')->exists())->toBeTrue();
    }

    public function test_creating_a_workspace_with_taken_code_is_rejected(): void
    {
        $me = User::factory()->create();
        app(WorkspaceService::class)->create($me, 'Tim Lain');

        $existing = Workspace::first();

        $this->actingAs($me)->postJson('/workspaces', ['name' => 'Tim Baru', 'code' => $existing->code])
            ->assertStatus(422)
            ->assertJsonPath('message', 'That code is already taken.');
    }

    public function test_creating_a_workspace_with_invites_sends_pending_invitations(): void
    {
        $me = User::factory()->create();
        $invitee = User::factory()->create(['username' => 'budi']);

        $this->actingAs($me)->postJson('/workspaces', [
            'name' => 'Tim Dev',
            'bio' => 'Tim pengembangan',
            'invites' => ['budi'],
        ])->assertOk();

        $workspace = Workspace::where('name', 'Tim Dev')->first();

        expect($workspace->bio)->toBe('Tim pengembangan');
        expect($workspace->members()->where('user_id', $invitee->id)->first()->pivot->status)->toBe('pending');
    }

    public function test_creating_a_workspace_rejects_unknown_invite(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->postJson('/workspaces', [
            'name' => 'Tim Dev',
            'invites' => ['nobody'],
        ])->assertStatus(422)
            ->assertJsonPath('message', "User 'nobody' not found.");

        expect(Workspace::count())->toBe(0);
    }

    public function test_creating_a_workspace_with_avatar(): void
    {
        Storage::fake('public');
        $me = User::factory()->create();

        $this->actingAs($me)->postJson('/workspaces', [
            'name' => 'Tim Dev',
            'avatar' => UploadedFile::fake()->image('logo.jpg', 200, 200),
        ])->assertOk();

        $workspace = Workspace::first();

        expect($workspace->avatar)->not->toBeNull();
        Storage::disk('public')->assertExists($workspace->avatar);
    }

    public function test_joining_a_workspace_by_code_is_case_insensitive(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($joiner)->postJson('/workspaces/join', ['code' => strtolower($workspace->code)])
            ->assertOk()
            ->assertJsonPath('code', $workspace->code);

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

    public function test_owner_can_invite_a_user_as_pending(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['username' => 'budi']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/members', ['identifier' => 'budi'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $pivot = $workspace->members()->where('user_id', $invitee->id)->first();

        expect($pivot)->not->toBeNull();
        expect($pivot->pivot->role)->toBe('user');
        expect($pivot->pivot->status)->toBe('pending');
        expect($pivot->pivot->inviter_id)->toBe($owner->id);
        expect($invitee->workspaces()->where('workspace_id', $workspace->id)->first()->pivot->status)->toBe('pending');
    }

    public function test_invite_accepts_at_prefix(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['username' => 'budi']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/members', ['identifier' => '@budi'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        expect($workspace->members()->where('user_id', $invitee->id)->first()->pivot->status)->toBe('pending');
    }

    public function test_creating_workspace_invite_accepts_at_prefix(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['username' => 'budi']);

        $this->actingAs($owner)->postJson('/workspaces', [
            'name' => 'Tim Dev',
            'invites' => ['@budi'],
        ])->assertOk();

        $workspace = Workspace::where('name', 'Tim Dev')->first();

        expect($workspace->members()->where('user_id', $invitee->id)->first()->pivot->status)->toBe('pending');
    }

    public function test_members_endpoint_excludes_pending_invitees(): void
    {
        $owner = User::factory()->create(['name' => 'Boss Owner']);
        $invitee = User::factory()->create(['username' => 'budi']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->invite($owner, $workspace->code, 'budi');

        $members = $this->actingAs($owner)->getJson('/workspaces/'.$workspace->code.'/members')
            ->assertOk()
            ->json();

        expect($members)->toHaveCount(1);
        expect($members[0]['name'])->toBe('Boss Owner');
    }

    public function test_invited_user_sees_pending_workspace_with_accept_reject(): void
    {
        $owner = User::factory()->create(['name' => 'Rina']);
        $invitee = User::factory()->create(['username' => 'budi']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->invite($owner, $workspace->code, 'budi');

        $html = $this->actingAs($invitee)->get('/messages')->getContent();

        expect($html)->toContain('Tim Dev');
        expect($html)->toContain('Rina invited you');
        expect($html)->toContain('Accept');
        expect($html)->toContain('Reject');
    }

    public function test_invitee_can_accept_invitation(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['username' => 'budi']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->invite($owner, $workspace->code, 'budi');

        $this->actingAs($invitee)->postJson('/workspaces/'.$workspace->code.'/invite-response', ['accept' => true])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('code', $workspace->code);

        expect($workspace->members()->where('user_id', $invitee->id)->first()->pivot->status)->toBe('member');
    }

    public function test_invitee_can_reject_invitation(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['username' => 'budi']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->invite($owner, $workspace->code, 'budi');

        $this->actingAs($invitee)->postJson('/workspaces/'.$workspace->code.'/invite-response', ['accept' => false])
            ->assertOk();

        expect($workspace->members()->where('user_id', $invitee->id)->exists())->toBeFalse();
    }

    public function test_regular_user_cannot_invite(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();
        $stranger = User::factory()->create(['username' => 'susi']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($joiner, $workspace->code);

        $this->actingAs($joiner)->postJson('/workspaces/'.$workspace->code.'/members', ['identifier' => 'susi'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Only the owner or an admin can add members.');
    }

    public function test_inviting_unknown_user_is_rejected(): void
    {
        $owner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/members', ['identifier' => 'nobody'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'User not found.');
    }

    public function test_inviting_existing_member_is_rejected(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create(['username' => 'budi']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($joiner, $workspace->code);

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/members', ['identifier' => 'budi'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'This user is already a member.');
    }

    public function test_duplicate_invite_is_rejected(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['username' => 'budi']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->invite($owner, $workspace->code, 'budi');

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/members', ['identifier' => 'budi'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'This user has already been invited.');
    }

    public function test_owner_can_invite_a_user_by_email(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'budi@example.com']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/members', ['identifier' => 'budi@example.com'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        expect($workspace->members()->where('user_id', $invitee->id)->first()->pivot->status)->toBe('pending');
    }

    public function test_respond_without_invitation_is_rejected(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($stranger)->postJson('/workspaces/'.$workspace->code.'/invite-response', ['accept' => true])
            ->assertStatus(422)
            ->assertJsonPath('message', 'No pending invitation.');
    }

    public function test_owner_can_promote_user_to_owner(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/members/'.$member->id.'/promote')
            ->assertOk();

        expect($workspace->members()->where('user_id', $member->id)->first()->pivot->role)->toBe('owner');
    }

    public function test_co_owner_can_promote_user_to_owner(): void
    {
        $owner = User::factory()->create();
        $co = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($co, $workspace->code);
        $workspace->members()->updateExistingPivot($co->id, ['role' => 'owner']);
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($co)->postJson('/workspaces/'.$workspace->code.'/members/'.$member->id.'/promote')
            ->assertOk();

        expect($workspace->members()->where('user_id', $member->id)->first()->pivot->role)->toBe('owner');
    }

    public function test_regular_user_cannot_promote(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $stranger = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);
        app(WorkspaceService::class)->join($stranger, $workspace->code);

        $this->actingAs($stranger)->postJson('/workspaces/'.$workspace->code.'/members/'.$member->id.'/promote')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Only an owner can manage members.');
    }

    public function test_owner_can_demote_co_owner(): void
    {
        $owner = User::factory()->create();
        $co = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($co, $workspace->code);
        $workspace->members()->updateExistingPivot($co->id, ['role' => 'owner']);

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/members/'.$co->id.'/demote')
            ->assertOk();

        expect($workspace->members()->where('user_id', $co->id)->first()->pivot->role)->toBe('user');
    }

    public function test_creator_cannot_be_demoted(): void
    {
        $owner = User::factory()->create();
        $co = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($co, $workspace->code);
        $workspace->members()->updateExistingPivot($co->id, ['role' => 'owner']);

        $this->actingAs($co)->postJson('/workspaces/'.$workspace->code.'/members/'.$owner->id.'/demote')
            ->assertStatus(422)
            ->assertJsonPath('message', 'The workspace creator cannot be demoted.');
    }

    public function test_owner_can_kick_a_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/members/kick', ['ids' => [$member->id]])
            ->assertOk();

        expect($workspace->members()->where('user_id', $member->id)->first()->pivot->status)->toBe('kicked');
    }

    public function test_owner_can_bulk_kick_members(): void
    {
        $owner = User::factory()->create();
        $a = User::factory()->create();
        $b = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($a, $workspace->code);
        app(WorkspaceService::class)->join($b, $workspace->code);

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/members/kick', ['ids' => [$a->id, $b->id]])
            ->assertOk();

        expect($workspace->members()->where('user_id', $a->id)->first()->pivot->status)->toBe('kicked');
        expect($workspace->members()->where('user_id', $b->id)->first()->pivot->status)->toBe('kicked');
        expect($workspace->members()->where('user_id', $owner->id)->first()->pivot->status)->toBe('member');
    }

    public function test_creator_cannot_be_kicked(): void
    {
        $owner = User::factory()->create();
        $co = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($co, $workspace->code);
        $workspace->members()->updateExistingPivot($co->id, ['role' => 'owner']);

        $this->actingAs($co)->postJson('/workspaces/'.$workspace->code.'/members/kick', ['ids' => [$owner->id]])
            ->assertStatus(422)
            ->assertJsonPath('message', 'No members to remove.');

        expect($workspace->members()->where('user_id', $owner->id)->exists())->toBeTrue();
    }

    public function test_user_can_leave_workspace(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($member)->postJson('/workspaces/'.$workspace->code.'/leave')
            ->assertOk();

        expect($workspace->members()->where('user_id', $member->id)->exists())->toBeFalse();
    }

    public function test_co_owner_can_leave_workspace(): void
    {
        $owner = User::factory()->create();
        $co = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($co, $workspace->code);
        $workspace->members()->updateExistingPivot($co->id, ['role' => 'owner']);

        $this->actingAs($co)->postJson('/workspaces/'.$workspace->code.'/leave')
            ->assertOk();

        expect($workspace->members()->where('user_id', $co->id)->exists())->toBeFalse();
        expect($workspace->refresh()->owner_id)->toBe($owner->id);
    }

    public function test_solo_creator_can_leave_and_workspace_is_deleted(): void
    {
        $owner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/leave')
            ->assertOk();

        expect(Workspace::find($workspace->id))->toBeNull();
        expect($owner->workspaces()->where('workspace_id', $workspace->id)->exists())->toBeFalse();
    }

    public function test_creator_with_members_must_delegate_before_leaving(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/leave')
            ->assertStatus(422)
            ->assertJsonPath('message', 'You must delegate ownership before leaving.');
    }

    public function test_creator_can_delegate_ownership_and_leave(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/leave', ['successor_id' => $member->id])
            ->assertOk();

        expect($workspace->refresh()->owner_id)->toBe($member->id);
        expect($workspace->members()->where('user_id', $member->id)->first()->pivot->role)->toBe('owner');
        expect($workspace->members()->where('user_id', $owner->id)->exists())->toBeFalse();
    }

    public function test_members_payload_includes_creator_profile_and_joined(): void
    {
        $owner = User::factory()->create(['name' => 'Boss Owner', 'bio' => 'Hello']);
        $member = User::factory()->create(['name' => 'Ayu Member']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $members = $this->actingAs($owner)->getJson('/workspaces/'.$workspace->code.'/members')
            ->assertOk()
            ->json();

        expect($members)->toHaveCount(2);
        expect($members[0]['creator'])->toBeTrue();
        expect($members[0]['is_me'])->toBeTrue();
        expect($members[0]['bio'])->toBe('Hello');
        expect($members[0]['joined'])->not->toBe('');
        expect($members[1]['creator'])->toBeFalse();
        expect($members[1]['is_me'])->toBeFalse();
    }

    public function test_joining_broadcasts_members_changed(): void
    {
        Event::fake([WorkspaceMembersChanged::class]);

        $owner = User::factory()->create();
        $joiner = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        app(WorkspaceService::class)->join($joiner, $workspace->code);

        Event::assertDispatched(WorkspaceMembersChanged::class, fn ($event) => $event->workspace->id === $workspace->id);
    }

    public function test_accepting_invite_broadcasts_members_changed(): void
    {
        Event::fake([WorkspaceMembersChanged::class]);

        $owner = User::factory()->create();
        $invitee = User::factory()->create(['username' => 'budi']);
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->invite($owner, $workspace->code, 'budi');

        app(WorkspaceService::class)->respondInvite($invitee, $workspace->code, true);

        Event::assertDispatched(WorkspaceMembersChanged::class, fn ($event) => $event->workspace->id === $workspace->id);
    }

    public function test_kicked_user_cannot_rejoin_by_code(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);
        app(WorkspaceService::class)->kick($owner, $workspace->code, [$member->id]);

        $this->actingAs($member)->postJson('/workspaces/join', ['code' => $workspace->code])
            ->assertStatus(422)
            ->assertJsonPath('message', 'You have been removed from this workspace. You can only rejoin if you are invited again.');

        expect($workspace->members()->where('user_id', $member->id)->first()->pivot->status)->toBe('kicked');
    }

    public function test_kicked_user_can_be_reinvited_and_accept(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);
        app(WorkspaceService::class)->kick($owner, $workspace->code, [$member->id]);

        $result = app(WorkspaceService::class)->invite($owner, $workspace->code, $member->username);

        expect($result['ok'])->toBeTrue();
        expect($workspace->members()->where('user_id', $member->id)->first()->pivot->status)->toBe('pending');

        app(WorkspaceService::class)->respondInvite($member, $workspace->code, true);

        expect($workspace->members()->where('user_id', $member->id)->first()->pivot->status)->toBe('member');
    }

    public function test_kicked_workspace_is_not_listed(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);
        app(WorkspaceService::class)->kick($owner, $workspace->code, [$member->id]);

        $html = $this->actingAs($member)->get('/messages')->getContent();

        expect($html)->not->toContain($workspace->code);
        expect($member->workspaces()->where('workspace_id', $workspace->id)->exists())->toBeTrue();
    }

    public function test_kicked_owner_cannot_configure_workspace(): void
    {
        $owner = User::factory()->create();
        $co = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($co, $workspace->code);
        $workspace->members()->updateExistingPivot($co->id, ['role' => 'owner']);
        app(WorkspaceService::class)->kick($owner, $workspace->code, [$co->id]);

        $this->actingAs($co)->postJson('/workspaces/'.$workspace->code.'/configure', ['bio' => 'ubah'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Only the owner or an admin can configure this workspace.');
    }

    public function test_member_can_send_workspace_message(): void
    {
        Event::fake([WorkspaceMessageSent::class]);

        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($member)->postJson('/workspaces/'.$workspace->code.'/messages', ['body' => 'Halo semua'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $message = WorkspaceMessage::first();

        expect($message)->not->toBeNull();
        expect($message->body)->toBe('Halo semua');
        expect($message->type)->toBe('text');
        expect($message->sender_id)->toBe($member->id);
        expect($message->workspace_id)->toBe($workspace->id);

        Event::assertDispatched(WorkspaceMessageSent::class);
    }

    public function test_non_member_cannot_send_workspace_message(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($stranger)->postJson('/workspaces/'.$workspace->code.'/messages', ['body' => 'Halo'])
            ->assertStatus(422);

        expect(WorkspaceMessage::count())->toBe(0);
    }

    public function test_workspace_message_rejects_unknown_file_type(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($member)->postJson('/workspaces/'.$workspace->code.'/messages', [
            'file' => UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload'),
        ])->assertStatus(422)
            ->assertJsonPath('message', 'File type not allowed.');
    }

    public function test_workspace_image_message_stores_file_and_preview(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($member)->postJson('/workspaces/'.$workspace->code.'/messages', [
            'file' => $this->largeImageUpload(),
        ])->assertOk();

        $message = WorkspaceMessage::first();

        expect($message->type)->toBe('image');
        expect($message->file_path)->not->toBeNull();
        expect($message->preview_path)->not->toBeNull();
        expect($message->width)->toBe(1600);
        expect($message->height)->toBe(1200);
        Storage::disk('public')->assertExists($message->file_path);
    }

    public function test_workspace_message_history_is_ordered_with_pagination(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        for ($i = 1; $i <= 30; $i++) {
            WorkspaceMessage::create([
                'workspace_id' => $workspace->id,
                'sender_id' => $owner->id,
                'body' => 'Pesan '.$i,
                'type' => 'text',
            ]);
        }

        $page = $this->actingAs($member)->getJson('/workspaces/'.$workspace->code.'/messages')
            ->assertOk()
            ->json();

        expect($page['has_more'])->toBeTrue();
        expect($page['messages'])->toHaveCount(25);
        expect($page['messages'][0]['body'])->toBe('Pesan 6');
        expect($page['messages'][24]['body'])->toBe('Pesan 30');

        $older = $this->actingAs($member)->getJson('/workspaces/'.$workspace->code.'/messages?before='.$page['messages'][0]['id'])
            ->assertOk()
            ->json();

        expect($older['messages'])->toHaveCount(5);
        expect($older['messages'][0]['body'])->toBe('Pesan 1');
        expect($older['has_more'])->toBeFalse();
    }

    public function test_non_member_cannot_read_workspace_messages(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');

        $this->actingAs($stranger)->getJson('/workspaces/'.$workspace->code.'/messages')
            ->assertStatus(404);
    }

    public function test_mark_read_and_list_meta_track_unread(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $m1 = WorkspaceMessage::create(['workspace_id' => $workspace->id, 'sender_id' => $owner->id, 'body' => 'A', 'type' => 'text']);
        $m2 = WorkspaceMessage::create(['workspace_id' => $workspace->id, 'sender_id' => $owner->id, 'body' => 'B', 'type' => 'text']);

        app(WorkspaceService::class)->markRead($member, $workspace->code);

        expect($workspace->members()->where('user_id', $member->id)->first()->pivot->last_read_message_id)->toBe($m2->id);

        $meta = app(WorkspaceService::class)->listMeta($member, app(WorkspaceService::class)->list($member));

        expect($meta[$workspace->id]['unread'])->toBe(0);
        expect($meta[$workspace->id]['last'])->toBe('B');
        expect($meta[$workspace->id]['sent'])->toBeFalse();

        $m3 = WorkspaceMessage::create(['workspace_id' => $workspace->id, 'sender_id' => $owner->id, 'body' => 'C', 'type' => 'text']);

        $meta = app(WorkspaceService::class)->listMeta($member, app(WorkspaceService::class)->list($member));

        expect($meta[$workspace->id]['unread'])->toBe(1);
        expect($meta[$workspace->id]['last'])->toBe('C');
        expect($meta[$workspace->id]['time'])->not->toBe('');
    }

    public function test_messages_page_renders_workspace_preview_and_unread(): void
    {
        $owner = User::factory()->create(['name' => 'Boss Owner']);
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);
        WorkspaceMessage::create(['workspace_id' => $workspace->id, 'sender_id' => $owner->id, 'body' => 'Halo tim', 'type' => 'text']);

        $html = $this->actingAs($member)->get('/messages')->getContent();

        expect($html)->toContain('Boss Owner : Halo tim');
        expect($html)->toContain('ws-unread');
        expect($html)->toContain('ws-unread-total');
    }

    public function test_member_typing_broadcasts_and_non_member_rejected(): void
    {
        Event::fake([WorkspaceTyping::class]);

        $owner = User::factory()->create();
        $member = User::factory()->create();
        $stranger = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($member)->postJson('/workspaces/'.$workspace->code.'/typing', ['typing' => true])
            ->assertOk();

        Event::assertDispatched(WorkspaceTyping::class, fn ($event) => $event->workspace->id === $workspace->id && $event->typing === true);

        $this->actingAs($stranger)->postJson('/workspaces/'.$workspace->code.'/typing', ['typing' => true])
            ->assertStatus(422);
    }

    public function test_workspace_message_read_flag_tracks_all_readers(): void
    {
        Event::fake([WorkspaceMessagesRead::class]);

        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);

        $this->actingAs($owner)->postJson('/workspaces/'.$workspace->code.'/messages', ['body' => 'Halo'])
            ->assertOk();

        $history = $this->actingAs($owner)->getJson('/workspaces/'.$workspace->code.'/messages')->json();

        expect($history['messages'][0]['read'])->toBeFalse();

        app(WorkspaceService::class)->markRead($member, $workspace->code);

        Event::assertDispatched(WorkspaceMessagesRead::class, fn ($event) => $event->workspace->id === $workspace->id && $event->readerId === $member->id);

        $history = $this->actingAs($owner)->getJson('/workspaces/'.$workspace->code.'/messages')->json();

        expect($history['messages'][0]['read'])->toBeTrue();
    }

    public function test_members_payload_includes_last_read_message_id(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(WorkspaceService::class)->create($owner, 'Tim Dev');
        app(WorkspaceService::class)->join($member, $workspace->code);
        WorkspaceMessage::create(['workspace_id' => $workspace->id, 'sender_id' => $owner->id, 'body' => 'A', 'type' => 'text']);

        $members = $this->actingAs($owner)->getJson('/workspaces/'.$workspace->code.'/members')->json();

        expect($members[0]['last_read_message_id'])->toBe(0);
        expect($members[1]['last_read_message_id'])->toBe(0);

        app(WorkspaceService::class)->markRead($member, $workspace->code);

        $members = $this->actingAs($owner)->getJson('/workspaces/'.$workspace->code.'/members')->json();

        expect($members[1]['last_read_message_id'])->toBeGreaterThan(0);
    }

    private function largeImageUpload(): UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'wsimg');
        $img = imagecreatetruecolor(1600, 1200);
        for ($x = 0; $x < 1600; $x++) {
            for ($y = 0; $y < 1200; $y++) {
                imagesetpixel($img, $x, $y, imagecolorallocate($img, $x % 255, $y % 255, 128));
            }
        }
        imagejpeg($img, $tmp, 90);
        imagedestroy($img);

        return new UploadedFile($tmp, 'foto.jpg', 'image/jpeg', null, true);
    }
}
