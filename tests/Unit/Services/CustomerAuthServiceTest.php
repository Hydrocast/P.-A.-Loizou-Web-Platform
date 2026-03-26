<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Enums\AccountStatus;
use App\Models\Customer;
use App\Services\CustomerAuthService;
use App\Services\EmailService;
use Mockery;
use Tests\TestCase;

/**
 * Unit tests for CustomerAuthService.
 *
 * Covers registration, login, password reset, and profile update.
 * Boundary values: name (2–50), password (8–64), phone (8 digits),
 * email (valid format, max 100 characters).
 */
class CustomerAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerAuthService $service;
    private EmailService $emailService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailService = Mockery::mock(EmailService::class);
        $this->service = new CustomerAuthService($this->emailService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // register()
    // -------------------------------------------------------------------------

    #[Test]
    /** Creates a customer account with Active status. */
    public function register_creates_active_customer(): void
    {
        Event::fake([Registered::class]);

        $customer = $this->service->register('test@example.com', 'password123', 'John Doe');

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals(AccountStatus::Active, $customer->account_status);
        $this->assertDatabaseHas('customers', ['email' => 'test@example.com']);

        Event::assertDispatched(Registered::class, function (Registered $event) use ($customer) {
            return $event->user->customer_id === $customer->customer_id;
        });
    }

    #[Test]
    /** Password is stored as a hash, not plain text. */
    public function register_hashes_the_password(): void
    {
        Event::fake([Registered::class]);

        $customer = $this->service->register('test@example.com', 'password123', 'John Doe');

        $this->assertNotEquals('password123', $customer->password);
        $this->assertTrue(Hash::check('password123', $customer->password));

        Event::assertDispatched(Registered::class);
    }

    #[Test]
    /** Duplicate email throws ValidationException. */
    public function register_throws_when_email_already_exists(): void
    {
        Customer::factory()->create(['email' => 'taken@example.com']);

        $this->expectException(ValidationException::class);
        $this->service->register('taken@example.com', 'password123', 'John Doe');
    }

    #[Test]
    /** Invalid email format throws ValidationException. */
    public function register_throws_when_email_is_invalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->register('not-an-email', 'password123', 'John Doe');
    }

    #[Test]
    /** Email exceeding 100 characters throws ValidationException. */
    public function register_throws_when_email_exceeds_one_hundred_characters(): void
    {
        $email = str_repeat('a', 92) . '@example.com';
        $this->expectException(ValidationException::class);
        $this->service->register($email, 'password123', 'John Doe');
    }

    // Name boundaries ---------------------------------------------------------

    #[Test]
    /** Full name of 1 character (below minimum) is rejected. */
    public function register_throws_when_full_name_is_one_character(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->register('a@example.com', 'password123', 'A');
    }

    #[Test]
    /** Full name of 2 characters (minimum) is accepted. */
    public function register_accepts_full_name_of_exactly_two_characters(): void
    {
        Event::fake([Registered::class]);

        $customer = $this->service->register('a@example.com', 'password123', 'Ab');

        $this->assertEquals('Ab', $customer->full_name);
        Event::assertDispatched(Registered::class);
    }

    #[Test]
    /** Full name of 26 characters (in-range) is accepted. */
    public function register_accepts_full_name_of_twenty_six_characters(): void
    {
        Event::fake([Registered::class]);

        $name = str_repeat('a', 26);
        $customer = $this->service->register('a@example.com', 'password123', $name);

        $this->assertEquals($name, $customer->full_name);
        Event::assertDispatched(Registered::class);
    }

    #[Test]
    /** Full name of 50 characters (maximum) is accepted. */
    public function register_accepts_full_name_of_fifty_characters(): void
    {
        Event::fake([Registered::class]);

        $name = str_repeat('a', 50);
        $customer = $this->service->register('a@example.com', 'password123', $name);

        $this->assertEquals($name, $customer->full_name);
        Event::assertDispatched(Registered::class);
    }

    #[Test]
    /** Full name of 51 characters (above maximum) is rejected. */
    public function register_throws_when_full_name_exceeds_fifty_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->register('a@example.com', 'password123', str_repeat('a', 51));
    }

    // Password boundaries -----------------------------------------------------

    #[Test]
    /** Password of 7 characters (below minimum) is rejected. */
    public function register_throws_when_password_is_seven_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->register('a@example.com', str_repeat('a', 7), 'John Doe');
    }

    #[Test]
    /** Password of 8 characters (minimum) is accepted. */
    public function register_accepts_password_of_exactly_eight_characters(): void
    {
        Event::fake([Registered::class]);

        $customer = $this->service->register('a@example.com', str_repeat('a', 8), 'John Doe');

        $this->assertNotNull($customer->customer_id);
        Event::assertDispatched(Registered::class);
    }

    #[Test]
    /** Password of 36 characters (in-range) is accepted. */
    public function register_accepts_password_of_thirty_six_characters(): void
    {
        Event::fake([Registered::class]);

        $customer = $this->service->register('a@example.com', str_repeat('a', 36), 'John Doe');

        $this->assertNotNull($customer->customer_id);
        Event::assertDispatched(Registered::class);
    }

    #[Test]
    /** Password of 64 characters (maximum) is accepted. */
    public function register_accepts_password_of_sixty_four_characters(): void
    {
        Event::fake([Registered::class]);

        $customer = $this->service->register('a@example.com', str_repeat('a', 64), 'John Doe');

        $this->assertNotNull($customer->customer_id);
        Event::assertDispatched(Registered::class);
    }

    #[Test]
    /** Password of 65 characters (above maximum) is rejected. */
    public function register_throws_when_password_exceeds_sixty_four_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->register('a@example.com', str_repeat('a', 65), 'John Doe');
    }

    // -------------------------------------------------------------------------
    // login()
    // -------------------------------------------------------------------------

    #[Test]
    /** Correct credentials return the customer. */
    public function login_returns_customer_on_correct_credentials(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'u@e.com',
            'password' => Hash::make('pass1234'),
        ]);

        $result = $this->service->login('u@e.com', 'pass1234');

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($customer->customer_id, $result->customer_id);
    }

    #[Test]
    /** Wrong password throws ValidationException. */
    public function login_throws_on_wrong_password(): void
    {
        Customer::factory()->create([
            'email' => 'u@e.com',
            'password' => Hash::make('correct123'),
        ]);

        $this->expectException(ValidationException::class);
        $this->service->login('u@e.com', 'wrong123');
    }

    #[Test]
    /** Nonexistent email throws ValidationException. */
    public function login_throws_on_nonexistent_email(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->login('nobody@e.com', 'pass1234');
    }

    #[Test]
    /** Inactive account throws ValidationException. */
    public function login_throws_validation_exception_when_account_is_inactive(): void
    {
        Customer::factory()->inactive()->create([
            'email' => 'u@e.com',
            'password' => Hash::make('pass1234'),
        ]);

        $this->expectException(ValidationException::class);
        $this->service->login('u@e.com', 'pass1234');
    }

    // -------------------------------------------------------------------------
    // requestPasswordReset()
    // -------------------------------------------------------------------------

    #[Test]
    /** Stores a hashed token and expiry for a known email. */
    public function request_password_reset_stores_hashed_token_for_known_email(): void
    {
        $customer = Customer::factory()->create(['email' => 'r@e.com']);

        $this->emailService
            ->shouldReceive('sendPasswordResetEmail')
            ->once()
            ->with($customer->email, Mockery::type('string'));

        $this->service->requestPasswordReset('r@e.com');

        $customer->refresh();
        $this->assertNotNull($customer->reset_token);
        $this->assertNotNull($customer->reset_token_expiry);
    }

    #[Test]
    /** Token expiry is set to 60 minutes from the time of the request. */
    public function request_password_reset_token_expires_in_sixty_minutes(): void
    {
        $customer = Customer::factory()->create(['email' => 'r@e.com']);

        $this->emailService
            ->shouldReceive('sendPasswordResetEmail')
            ->once()
            ->with($customer->email, Mockery::type('string'));

        $this->service->requestPasswordReset('r@e.com');

        $customer->refresh();
        $this->assertEqualsWithDelta(
            now()->addMinutes(60)->timestamp,
            $customer->reset_token_expiry->timestamp,
            5,
        );
    }

    #[Test]
    /** Token is stored as a hash, not plain text. */
    public function request_password_reset_stores_token_as_hash(): void
    {
        $customer = Customer::factory()->create(['email' => 'r@e.com']);

        $this->emailService
            ->shouldReceive('sendPasswordResetEmail')
            ->once()
            ->with($customer->email, Mockery::type('string'));

        $this->service->requestPasswordReset('r@e.com');

        $customer->refresh();
        $this->assertStringStartsWith('$2y$', $customer->reset_token);
    }

    #[Test]
    /** Unknown email returns silently with no exception and no database changes. */
    public function request_password_reset_returns_silently_for_unknown_email(): void
    {
        $this->service->requestPasswordReset('nobody@e.com');
        $this->assertDatabaseCount('customers', 0);
    }

    #[Test]
    /** Invalid email format throws ValidationException. */
    public function request_password_reset_throws_when_email_is_invalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->requestPasswordReset('not-an-email');
    }

    #[Test]
    /** Email exceeding 100 characters throws ValidationException. */
    public function request_password_reset_throws_when_email_exceeds_one_hundred_characters(): void
    {
        $email = str_repeat('a', 92) . '@example.com';
        $this->expectException(ValidationException::class);
        $this->service->requestPasswordReset($email);
    }

    // -------------------------------------------------------------------------
    // resetPassword()
    // -------------------------------------------------------------------------

    #[Test]
    /** Password is updated when a valid token is provided. */
    public function reset_password_updates_password_with_valid_token(): void
    {
        $raw = 'valid-token';
        $customer = Customer::factory()->create([
            'email' => 'r@e.com',
            'reset_token' => Hash::make($raw),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);

        $this->service->resetPassword('r@e.com', $raw, 'newpassword1');

        $customer->refresh();
        $this->assertTrue(Hash::check('newpassword1', $customer->password));
    }

    #[Test]
    /** Reset token and expiry are cleared after a successful password reset. */
    public function reset_password_clears_token_after_successful_reset(): void
    {
        $raw = 'valid-token';
        $customer = Customer::factory()->create([
            'email' => 'r@e.com',
            'reset_token' => Hash::make($raw),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);

        $this->service->resetPassword('r@e.com', $raw, 'newpassword1');

        $customer->refresh();
        $this->assertNull($customer->reset_token);
        $this->assertNull($customer->reset_token_expiry);
    }

    #[Test]
    /** Expired token throws ValidationException. */
    public function reset_password_throws_on_expired_token(): void
    {
        $raw = 'expired-token';

        Customer::factory()->create([
            'email' => 'r@e.com',
            'reset_token' => Hash::make($raw),
            'reset_token_expiry' => now()->subMinute(),
        ]);

        $this->expectException(ValidationException::class);
        $this->service->resetPassword('r@e.com', $raw, 'newpassword1');
    }

    #[Test]
    /** Invalid token throws ValidationException. */
    public function reset_password_throws_on_invalid_token(): void
    {
        Customer::factory()->create([
            'email' => 'r@e.com',
            'reset_token' => Hash::make('correct-token'),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);

        $this->expectException(ValidationException::class);
        $this->service->resetPassword('r@e.com', 'wrong-token', 'newpassword1');
    }

    #[Test]
    /** Nonexistent email throws ValidationException. */
    public function reset_password_throws_when_email_does_not_exist(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->resetPassword('nobody@e.com', 'any-token', 'newpassword1');
    }

    #[Test]
    /** Account with no token issued throws ValidationException. */
    public function reset_password_throws_when_no_token_has_been_issued(): void
    {
        Customer::factory()->create([
            'email' => 'r@e.com',
            'reset_token' => null,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->resetPassword('r@e.com', 'any-token', 'newpassword1');
    }

    // Password boundaries -----------------------------------------------------

    #[Test]
    /** New password of 7 characters (below minimum) is rejected. */
    public function reset_password_throws_when_new_password_is_seven_characters(): void
    {
        $raw = 'valid-token';

        Customer::factory()->create([
            'email' => 'r@e.com',
            'reset_token' => Hash::make($raw),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);

        $this->expectException(ValidationException::class);
        $this->service->resetPassword('r@e.com', $raw, str_repeat('a', 7));
    }

    #[Test]
    /** New password of 8 characters (minimum) is accepted. */
    public function reset_password_accepts_new_password_of_eight_characters(): void
    {
        $raw = 'valid-token';
        $customer = Customer::factory()->create([
            'email' => 'r@e.com',
            'reset_token' => Hash::make($raw),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);

        $this->service->resetPassword('r@e.com', $raw, str_repeat('a', 8));

        $customer->refresh();
        $this->assertTrue(Hash::check(str_repeat('a', 8), $customer->password));
    }

    #[Test]
    /** New password of 36 characters (in-range) is accepted. */
    public function reset_password_accepts_new_password_of_thirty_six_characters(): void
    {
        $raw = 'valid-token';
        $customer = Customer::factory()->create([
            'email' => 'r@e.com',
            'reset_token' => Hash::make($raw),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);

        $this->service->resetPassword('r@e.com', $raw, str_repeat('a', 36));

        $customer->refresh();
        $this->assertTrue(Hash::check(str_repeat('a', 36), $customer->password));
    }

    #[Test]
    /** New password of 64 characters (maximum) is accepted. */
    public function reset_password_accepts_new_password_of_sixty_four_characters(): void
    {
        $raw = 'valid-token';
        $customer = Customer::factory()->create([
            'email' => 'r@e.com',
            'reset_token' => Hash::make($raw),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);

        $this->service->resetPassword('r@e.com', $raw, str_repeat('a', 64));

        $customer->refresh();
        $this->assertTrue(Hash::check(str_repeat('a', 64), $customer->password));
    }

    #[Test]
    /** New password of 65 characters (above maximum) is rejected. */
    public function reset_password_throws_when_new_password_exceeds_sixty_four_characters(): void
    {
        $raw = 'valid-token';

        Customer::factory()->create([
            'email' => 'r@e.com',
            'reset_token' => Hash::make($raw),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);

        $this->expectException(ValidationException::class);
        $this->service->resetPassword('r@e.com', $raw, str_repeat('a', 65));
    }

    // -------------------------------------------------------------------------
    // updateProfile()
    // -------------------------------------------------------------------------

    #[Test]
    /** Full name and phone number are updated to the new values. */
    public function update_profile_changes_full_name_and_phone(): void
    {
        $customer = Customer::factory()->create();

        $this->service->updateProfile($customer->customer_id, 'New Name', '12345678');

        $customer->refresh();
        $this->assertEquals('New Name', $customer->full_name);
        $this->assertEquals('12345678', $customer->phone_number);
    }

    #[Test]
    /** Null phone number clears the existing value. */
    public function update_profile_accepts_null_phone_number(): void
    {
        $customer = Customer::factory()->create(['phone_number' => '12345678']);

        $this->service->updateProfile($customer->customer_id, 'Name', null);

        $customer->refresh();
        $this->assertNull($customer->phone_number);
    }

    #[Test]
    /** Nonexistent customer ID throws ModelNotFoundException. */
    public function update_profile_throws_when_customer_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->updateProfile(99999, 'Name', null);
    }

    // Name boundaries ---------------------------------------------------------

    #[Test]
    /** Full name of 1 character (below minimum) is rejected. */
    public function update_profile_throws_when_full_name_is_one_character(): void
    {
        $customer = Customer::factory()->create();

        $this->expectException(ValidationException::class);
        $this->service->updateProfile($customer->customer_id, 'A', null);
    }

    #[Test]
    /** Full name of 2 characters (minimum) is accepted. */
    public function update_profile_accepts_full_name_of_two_characters(): void
    {
        $customer = Customer::factory()->create();

        $this->service->updateProfile($customer->customer_id, 'Ab', null);

        $customer->refresh();
        $this->assertEquals('Ab', $customer->full_name);
    }

    #[Test]
    /** Full name of 26 characters (in-range) is accepted. */
    public function update_profile_accepts_full_name_of_twenty_six_characters(): void
    {
        $customer = Customer::factory()->create();
        $name = str_repeat('a', 26);

        $this->service->updateProfile($customer->customer_id, $name, null);

        $customer->refresh();
        $this->assertEquals($name, $customer->full_name);
    }

    #[Test]
    /** Full name of 50 characters (maximum) is accepted. */
    public function update_profile_accepts_full_name_of_fifty_characters(): void
    {
        $customer = Customer::factory()->create();
        $name = str_repeat('a', 50);

        $this->service->updateProfile($customer->customer_id, $name, null);

        $customer->refresh();
        $this->assertEquals($name, $customer->full_name);
    }

    #[Test]
    /** Full name of 51 characters (above maximum) is rejected. */
    public function update_profile_throws_when_full_name_exceeds_fifty_characters(): void
    {
        $customer = Customer::factory()->create();

        $this->expectException(ValidationException::class);
        $this->service->updateProfile($customer->customer_id, str_repeat('a', 51), null);
    }

    // Phone number boundaries -------------------------------------------------

    #[Test]
    /** Phone number of 7 digits (below required length) is rejected. */
    public function update_profile_throws_when_phone_is_seven_digits(): void
    {
        $customer = Customer::factory()->create();

        $this->expectException(ValidationException::class);
        $this->service->updateProfile($customer->customer_id, 'Valid Name', '1234567');
    }

    #[Test]
    /** Phone number of 8 digits (required length) is accepted. */
    public function update_profile_accepts_eight_digit_phone(): void
    {
        $customer = Customer::factory()->create();

        $this->service->updateProfile($customer->customer_id, 'Valid Name', '12345678');

        $customer->refresh();
        $this->assertEquals('12345678', $customer->phone_number);
    }

    #[Test]
    /** Phone number of 9 digits (above required length) is rejected. */
    public function update_profile_throws_when_phone_is_nine_digits(): void
    {
        $customer = Customer::factory()->create();

        $this->expectException(ValidationException::class);
        $this->service->updateProfile($customer->customer_id, 'Valid Name', '123456789');
    }

    #[Test]
    /** Phone number containing non-digit characters is rejected. */
    public function update_profile_throws_when_phone_contains_non_digit_characters(): void
    {
        $customer = Customer::factory()->create();

        $this->expectException(ValidationException::class);
        $this->service->updateProfile($customer->customer_id, 'Valid Name', '1234567a');
    }

    // -------------------------------------------------------------------------
    // logout()
    // -------------------------------------------------------------------------

    #[Test]
    /** Logout completes without throwing an exception. */
    public function logout_completes_without_exception(): void
    {
        $this->service->logout();
        $this->assertTrue(true);
    }
}