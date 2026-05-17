<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use RyanChandler\LaravelCloudflareTurnstile\Facades\Turnstile;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function registration_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    {
        if (Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function new_users_can_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $fake = Turnstile::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'cf-turnstile-response' => $fake->dummy(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('players.index', absolute: false));
    }

    /**
     * @test
     */
    public function new_users_without_turnstile_cannot_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        Turnstile::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
    }
}
