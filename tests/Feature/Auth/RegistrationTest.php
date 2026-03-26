<?php

use App\Models\Customer;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new customers can register', function () {
    $response = $this->post(route('register'), [
        'full_name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated('customer');
    $response->assertRedirect(route('home', absolute: false));

    $this->assertDatabaseHas('customers', [
        'email' => 'test@example.com',
        'full_name' => 'Test User',
    ]);
});

test('registration fails when email already exists', function () {
    Customer::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $response = $this->from(route('register'))->post(route('register'), [
        'full_name' => 'Test User',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertGuest('customer');
    $response->assertRedirect(route('register', absolute: false));
    $response->assertSessionHasErrors('email');
});