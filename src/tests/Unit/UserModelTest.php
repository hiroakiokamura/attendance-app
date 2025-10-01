<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_user_has_many_attendances()
    {
        $user = User::factory()->create();
        Attendance::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->attendances);
        $this->assertInstanceOf(Attendance::class, $user->attendances->first());
    }

    /** @test */
    public function test_user_has_many_stamp_correction_requests()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        StampCorrectionRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);

        $this->assertCount(2, $user->stampCorrectionRequests);
        $this->assertInstanceOf(StampCorrectionRequest::class, $user->stampCorrectionRequests->first());
    }

    /** @test */
    public function test_user_has_many_approved_requests()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        
        StampCorrectionRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approved_by' => $admin->id,
        ]);

        $this->assertCount(2, $admin->approvedRequests);
        $this->assertInstanceOf(StampCorrectionRequest::class, $admin->approvedRequests->first());
    }

    /** @test */
    public function test_fillable_attributes()
    {
        $fillable = [
            'name',
            'email',
            'password',
            'is_admin',
        ];

        $user = new User();
        
        $this->assertEquals($fillable, $user->getFillable());
    }

    /** @test */
    public function test_hidden_attributes()
    {
        $hidden = [
            'password',
            'remember_token',
        ];

        $user = new User();
        
        $this->assertEquals($hidden, $user->getHidden());
    }

    /** @test */
    public function test_password_cast()
    {
        $user = new User();
        $casts = $user->getCasts();
        
        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('hashed', $casts['password']);
    }

    /** @test */
    public function test_email_verified_at_cast()
    {
        $user = new User();
        $casts = $user->getCasts();
        
        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }

    /** @test */
    public function test_is_admin_cast()
    {
        $user = new User();
        $casts = $user->getCasts();
        
        $this->assertArrayHasKey('is_admin', $casts);
        $this->assertEquals('boolean', $casts['is_admin']);
    }

    /** @test */
    public function test_admin_user_creation()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertTrue($admin->is_admin);
        $this->assertInstanceOf(User::class, $admin);
    }

    /** @test */
    public function test_regular_user_creation()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->assertFalse($user->is_admin);
        $this->assertInstanceOf(User::class, $user);
    }

    /** @test */
    public function test_default_is_admin_value()
    {
        $user = User::factory()->create(); // is_adminを指定しない

        // デフォルトではfalse（一般ユーザー）
        $this->assertFalse($user->is_admin);
    }

    /** @test */
    public function test_user_can_be_searched_by_name()
    {
        User::factory()->create(['name' => '田中太郎', 'is_admin' => false]);
        User::factory()->create(['name' => '佐藤花子', 'is_admin' => false]);
        User::factory()->create(['name' => '鈴木一郎', 'is_admin' => false]);

        $users = User::where('name', 'LIKE', '%田中%')->get();

        $this->assertCount(1, $users);
        $this->assertEquals('田中太郎', $users->first()->name);
    }

    /** @test */
    public function test_user_can_be_searched_by_email()
    {
        User::factory()->create(['email' => 'tanaka@example.com']);
        User::factory()->create(['email' => 'sato@example.com']);

        $user = User::where('email', 'tanaka@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('tanaka@example.com', $user->email);
    }

    /** @test */
    public function test_admin_users_can_be_filtered()
    {
        User::factory()->count(3)->create(['is_admin' => false]);
        User::factory()->count(2)->create(['is_admin' => true]);

        $admins = User::where('is_admin', true)->get();
        $regularUsers = User::where('is_admin', false)->get();

        $this->assertCount(2, $admins);
        $this->assertCount(3, $regularUsers);
    }

    /** @test */
    public function test_user_password_is_hashed()
    {
        $user = User::factory()->create(['password' => 'testpassword']);

        // パスワードがハッシュ化されていることを確認
        $this->assertNotEquals('testpassword', $user->password);
        $this->assertTrue(\Hash::check('testpassword', $user->password));
    }
}
