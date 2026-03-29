<?php

use App\Http\Controllers\Staff\OrderManagementController;
use App\Services\OrderProcessingService;

afterEach(function () {
    \Mockery::close();
});

test('it resolves unique clipart names from a design snapshot', function () {
    $controller = new OrderManagementController(
        \Mockery::mock(OrderProcessingService::class),
    );

    $method = new ReflectionMethod(OrderManagementController::class, 'resolveClipartNames');
    $method->setAccessible(true);

    $designSnapshot = json_encode([
        'objects' => [
            ['type' => 'image', 'src' => '/images/clipart/star.png'],
            ['type' => 'image', 'src' => '/images/clipart/heart.png'],
            ['type' => 'image', 'src' => '/images/clipart/star.png'],
            ['type' => 'image', 'src' => '/images/uploads/custom.png'],
            ['type' => 'textbox', 'text' => 'Hello'],
        ],
    ], JSON_UNESCAPED_SLASHES);

    $result = $method->invoke(
        $controller,
        $designSnapshot,
        [
            '/images/clipart/star.png' => 'Star',
            '/images/clipart/heart.png' => 'Heart',
        ],
    );

    expect($result)->toBe(['Star', 'Heart']);
});

test('it returns an empty array when no clipart names can be resolved', function () {
    $controller = new OrderManagementController(
        \Mockery::mock(OrderProcessingService::class),
    );

    $method = new ReflectionMethod(OrderManagementController::class, 'resolveClipartNames');
    $method->setAccessible(true);

    $result = $method->invoke(
        $controller,
        json_encode([
            'objects' => [
                ['type' => 'image', 'src' => '/images/uploads/custom.png'],
                ['type' => 'textbox', 'text' => 'Hello'],
            ],
        ], JSON_UNESCAPED_SLASHES),
        [
            '/images/clipart/star.png' => 'Star',
        ],
    );

    expect($result)->toBe([]);
});
