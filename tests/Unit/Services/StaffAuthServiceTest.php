<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Staff;
use App\Services\StaffAuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests for StaffAuthService.
 *
 * Covers staff login and logout functionality.
 */
class StaffAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private StaffAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StaffAuthService();
    }

    // -------------------------------------------------------------------------
    // login()
    // -------------------------------------------------------------------------

    #[Test]
    /** Correct username and password return the staff member. */
    public function login_returns_staff_on_correct_credentials(): void
    {
        $staff = Staff::factory()->create([
            'username' => 'staffuser',
            'password' => Hash::make('password123'),
        ]);

        $result = $this->service->login('staffuser', 'password123');

        $this->assertInstanceOf(Staff::class, $result);
        $this->assertEquals($staff->staff_id, $result->staff_id);
    }

    #[Test]
    /** Wrong password throws ValidationException. */
    public function login_throws_on_wrong_password(): void
    {
        Staff::factory()->create([
            'username' => 'staffuser',
            'password' => Hash::make('correct'),
        ]);

        $this->expectException(ValidationException::class);
        $this->service->login('staffuser', 'wrong');
    }

    #[Test]
    /** Non-existent username throws ValidationException. */
    public function login_throws_on_nonexistent_username(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->login('nobody', 'password123');
    }

    #[Test]
    /** Inactive account throws AuthenticationException. */
    public function login_throws_authentication_exception_when_account_is_inactive(): void
    {
        Staff::factory()->inactive()->create([
            'username' => 'staffuser',
            'password' => Hash::make('password123'),
        ]);

        $this->expectException(AuthenticationException::class);
        $this->service->login('staffuser', 'password123');
    }

    // -------------------------------------------------------------------------
    // logout()
    // -------------------------------------------------------------------------

    #[Test]
    /** Logout completes without throwing any exception. */
    public function logout_completes_without_exception(): void
    {
        $this->service->logout();
        $this->assertTrue(true);
    }
}