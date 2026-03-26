<?php

use App\Enums\AccountStatus;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('customers can authenticate using the login screen', function () {
    $customer = Customer::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'account_status' => AccountStatus::Active,
    ]);

    $response = $this->post(route('login'), [
        'email' => $customer->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticated('customer');
    $response->assertRedirect(route('home', absolute: false));
});

test('customers can not authenticate with invalid password', function () {
    $customer = Customer::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'account_status' => AccountStatus::Active,
    ]);

    $response = $this->from(route('login'))->post(route('login'), [
        'email' => $customer->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest('customer');
    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHasErrors('email');
});

test('inactive customers can not authenticate', function () {
    $customer = Customer::factory()->inactive()->create([
        'email' => 'inactive@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->from(route('login'))->post(route('login'), [
        'email' => $customer->email,
        'password' => 'password123',
    ]);

    $this->assertGuest('customer');
    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHasErrors('email');
});

test('customers can logout', function () {
    $customer = Customer::factory()->create([
        'account_status' => AccountStatus::Active,
    ]);

    $response = $this->actingAs($customer, 'customer')
        ->post(route('customer.logout'));

    $this->assertGuest('customer');
    $response->assertRedirect(route('home', absolute: false));
});