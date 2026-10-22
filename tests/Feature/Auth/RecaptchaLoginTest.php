<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('recaptcha.enabled', true);
        Config::set('recaptcha.site_key', 'test-site-key');
        Config::set('recaptcha.secret_key', 'test-secret-key-12345');
        Config::set('recaptcha.login_action', 'login');
        Config::set('recaptcha.login_score', 0.5);
        Config::set('recaptcha.allowed_hostnames', ['barbossa.nagakecil.site', 'localhost', '127.0.0.1']);
    }

    /**
     * Test 1: login without recaptcha token is rejected.
     */
    public function test_login_without_recaptcha_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();
    }

    /**
     * Test 2: invalid recaptcha token is rejected.
     */
    public function test_invalid_recaptcha_token_is_rejected(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'invalid-token',
        ]);

        $response->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();
    }

    /**
     * Test 3: failed Google verification is handled safely without 500.
     */
    public function test_failed_google_verification_is_handled_safely(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(null, 500),
        ]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'some-token',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();
    }

    /**
     * Test 4: low score is rejected.
     */
    public function test_low_score_is_rejected(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.3,
                'action' => 'login',
                'hostname' => 'barbossa.nagakecil.site',
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'bot-token',
        ]);

        $response->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();
    }

    /**
     * Test 5: wrong action is rejected.
     */
    public function test_wrong_action_is_rejected(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
                'action' => 'register',
                'hostname' => 'barbossa.nagakecil.site',
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'action-mismatch-token',
        ]);

        $response->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();
    }

    /**
     * Test 6: wrong hostname is rejected.
     */
    public function test_wrong_hostname_is_rejected(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
                'action' => 'login',
                'hostname' => 'malicious-domain.com',
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'wrong-host-token',
        ]);

        $response->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();
    }

    /**
     * Test 7: valid recaptcha allows normal authentication.
     */
    public function test_valid_recaptcha_allows_normal_authentication(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
                'action' => 'login',
                'hostname' => 'barbossa.nagakecil.site',
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'valid-token',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Test 8: recaptcha secret never appears in response.
     */
    public function test_recaptcha_secret_never_appears_in_response(): void
    {
        $loginPage = $this->get('/login');
        $loginPage->assertStatus(200);
        $loginPage->assertDontSee('test-secret-key-12345');
        $loginPage->assertSee('test-site-key');

        $failResponse = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong',
            'g-recaptcha-response' => 'invalid',
        ]);
        $failResponse->assertDontSee('test-secret-key-12345');
    }

    /**
     * Test 9: existing login remains functional.
     */
    public function test_existing_login_remains_functional(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
                'action' => 'login',
                'hostname' => 'localhost',
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'valid-user-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Test 10: existing logout remains functional.
     */
    public function test_existing_logout_remains_functional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
