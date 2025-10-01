<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\EmailVerificationToken;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Carbon\Carbon;

class CustomEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_email_verification_screen_can_be_rendered()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get('/email/verify-custom');

        $response->assertStatus(200);
        $response->assertViewIs('auth.verify-email-custom');
    }

    /** @test */
    public function test_verified_user_redirected_from_verification_screen()
    {
        $user = User::factory()->create(['email_verified_at' => Carbon::now()]);

        $response = $this->actingAs($user)->get('/email/verify-custom');

        $response->assertRedirect('/attendance');
    }

    /** @test */
    public function test_email_verification_notification_can_be_resent()
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->post('/email/verify-custom/resend');

        $response->assertRedirect('/email/verify-custom');
        $response->assertSessionHas('status', 'verification-link-sent');

        // トークンが作成されていることを確認
        $this->assertDatabaseHas('email_verification_tokens', [
            'email' => $user->email,
        ]);

        // 通知が送信されていることを確認
        Notification::assertSentTo($user, EmailVerificationNotification::class);
    }

    /** @test */
    public function test_email_can_be_verified_with_valid_token()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        
        $token = EmailVerificationToken::create([
            'email' => $user->email,
            'token' => 'valid-token',
            'expires_at' => Carbon::now()->addHours(24),
        ]);

        $response = $this->get("/email/verify-custom/{$user->email}/valid-token");

        $response->assertRedirect('/attendance');
        $response->assertSessionHas('success', 'メールアドレスの認証が完了しました。');

        // ユーザーが認証済みになっていることを確認
        $this->assertNotNull($user->fresh()->email_verified_at);

        // トークンが削除されていることを確認
        $this->assertDatabaseMissing('email_verification_tokens', [
            'id' => $token->id,
        ]);

        // ユーザーがログインしていることを確認
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function test_email_cannot_be_verified_with_invalid_token()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->get("/email/verify-custom/{$user->email}/invalid-token");

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', '認証リンクが無効または期限切れです。');

        // ユーザーが未認証のままであることを確認
        $this->assertNull($user->fresh()->email_verified_at);
    }

    /** @test */
    public function test_email_cannot_be_verified_with_expired_token()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        
        EmailVerificationToken::create([
            'email' => $user->email,
            'token' => 'expired-token',
            'expires_at' => Carbon::now()->subHours(1), // 期限切れ
        ]);

        $response = $this->get("/email/verify-custom/{$user->email}/expired-token");

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', '認証リンクが無効または期限切れです。');

        // ユーザーが未認証のままであることを確認
        $this->assertNull($user->fresh()->email_verified_at);
    }

    /** @test */
    public function test_unverified_user_redirected_to_verification_screen()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertRedirect('/email/verify-custom');
    }

    /** @test */
    public function test_verified_user_can_access_protected_routes()
    {
        $user = User::factory()->create(['email_verified_at' => Carbon::now()]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_registration_sends_verification_email()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/email/verify-custom');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        // 認証トークンが作成されていることを確認
        $this->assertDatabaseHas('email_verification_tokens', [
            'email' => 'test@example.com',
        ]);

        // 認証メールが送信されていることを確認
        Notification::assertSentTo($user, EmailVerificationNotification::class);
    }

    /** @test */
    public function test_expired_tokens_can_be_cleaned_up()
    {
        // 期限切れトークンを作成
        EmailVerificationToken::create([
            'email' => 'test1@example.com',
            'token' => 'expired-token-1',
            'expires_at' => Carbon::now()->subHours(1),
        ]);

        // 有効なトークンを作成
        EmailVerificationToken::create([
            'email' => 'test2@example.com',
            'token' => 'valid-token',
            'expires_at' => Carbon::now()->addHours(1),
        ]);

        $deletedCount = EmailVerificationToken::deleteExpired();

        $this->assertEquals(1, $deletedCount);
        $this->assertDatabaseMissing('email_verification_tokens', [
            'token' => 'expired-token-1',
        ]);
        $this->assertDatabaseHas('email_verification_tokens', [
            'token' => 'valid-token',
        ]);
    }
}
