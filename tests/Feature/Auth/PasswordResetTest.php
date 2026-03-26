<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

test('forgot password screen can be rendered', function () {
    $response = $this->get(route('customer.password.request'));

    $response->assertOk();
});

test('password reset link can be requested for existing customer', function () {
    $customer = Customer::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->from(route('customer.password.request'))->post(route('customer.password.email'), [
        'email' => $customer->email,
    ]);

    $response->assertRedirect(route('customer.password.request', absolute: false));
    $response->assertSessionHas('status');

    $customer->refresh();

    expect($customer->reset_token)->not->toBeNull();
    expect($customer->reset_token_expiry)->not->toBeNull();
});

test('password reset link request still succeeds for unknown email', function () {
    $response = $this->from(route('customer.password.request'))->post(route('customer.password.email'), [
        'email' => 'unknown@example.com',
    ]);

    $response->assertRedirect(route('customer.password.request', absolute: false));
    $response->assertSessionHas('status');
});

test('reset password screen can be rendered', function () {
    $response = $this->get(route('customer.password.reset', [
        'token' => 'sample-token',
        'email' => 'test@example.com',
    ]));

    $response->assertOk();
});

test('password can be reset with valid token', function () {
    $rawToken = 'valid-reset-token';

    $customer = Customer::factory()->create([
        'email' => 'test@example.com',
        'reset_token' => Hash::make($rawToken),
        'reset_token_expiry' => now()->addMinutes(60),
        'password' => Hash::make('oldpassword123'),
    ]);

    $response = $this->post(route('customer.password.update', [
        'token' => $rawToken,
    ]), [
        'token' => $rawToken,
        'email' => $customer->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('status');

    $customer->refresh();

    expect(Hash::check('newpassword123', $customer->password))->toBeTrue();
    expect($customer->reset_token)->toBeNull();
    expect($customer->reset_token_expiry)->toBeNull();
});

test('password cannot be reset with invalid token', function () {
    $customer = Customer::factory()->create([
        'email' => 'test@example.com',
        'reset_token' => Hash::make('correct-token'),
        'reset_token_expiry' => now()->addMinutes(60),
    ]);

    $response = $this->from(route('customer.password.reset', [
        'token' => 'wrong-token',
        'email' => $customer->email,
    ]))->post(route('customer.password.update', [
        'token' => 'wrong-token',
    ]), [
        'token' => 'wrong-token',
        'email' => $customer->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors();
});