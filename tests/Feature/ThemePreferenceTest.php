<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemePreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_render_blocking_theme_script_and_toggle(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('connected_theme', false);
        $response->assertSee('prefers-color-scheme', false);
        $response->assertSee('title="Dark theme"', false);
    }

    public function test_authenticated_pages_render_blocking_theme_script_and_toggle(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('connected_theme', false);
        $response->assertSee('title="Dark theme"', false);
    }
}
