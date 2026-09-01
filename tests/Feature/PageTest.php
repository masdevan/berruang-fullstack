<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_guest_to_login_view(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Sign in', false)
            ->assertSee('login');
    }

    public function test_home_redirects_authenticated_user_to_messages(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->get('/')
            ->assertRedirect('/messages');
    }

    public function test_check_username_reports_availability(): void
    {
        User::factory()->create(['username' => 'taken_user']);

        $this->get('/check-username/taken_user')
            ->assertOk()
            ->assertJson(['taken' => true]);

        $this->get('/check-username/free_user')
            ->assertOk()
            ->assertJson(['taken' => false]);
    }

    public function test_landing_page_is_public(): void
    {
        $this->get('/landing')
            ->assertOk()
            ->assertSee('Get started');
    }

    public function test_auth_google_redirects_to_login(): void
    {
        $this->get('/auth/google')
            ->assertRedirect('/login');
    }
}
