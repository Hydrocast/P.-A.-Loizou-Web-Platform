<?php

use App\Support\DesignProfileRegistry;
use Tests\TestCase;

uses(TestCase::class);

test('it returns structured product details for a valid profile key', function () {
    $details = DesignProfileRegistry::getProductDetails('tshirt-classic');

    expect($details)
        ->toBeArray()
        ->and($details['type'] ?? null)->toBe('size_guide')
        ->and($details['title'] ?? null)->toBe('Unisex T-Shirts Size Guide')
        ->and($details['columns'] ?? null)->toBe(['Size', 'Width', 'Length'])
        ->and($details['rows'] ?? null)->toBeArray();
});

test('it returns null product details for unknown profile keys', function () {
    $details = DesignProfileRegistry::getProductDetails('unknown-profile');

    expect($details)->toBeNull();
});

test('it returns null product details when profile key is null', function () {
    $details = DesignProfileRegistry::getProductDetails(null);

    expect($details)->toBeNull();
});

test('it returns workspace options for a valid t-shirt profile', function () {
    $workspaceOptions = DesignProfileRegistry::getWorkspaceOptions('tshirt-classic');

    expect($workspaceOptions)
        ->toBeArray()
        ->and($workspaceOptions['print_sides']['enabled'] ?? null)->toBeTrue();
});

test('it resolves requested print side when valid', function () {
    $workspaceOptions = DesignProfileRegistry::getWorkspaceOptions('tshirt-classic');

    $resolved = DesignProfileRegistry::resolveSelectedPrintSide('front_and_back', $workspaceOptions);

    expect($resolved)
        ->toBeArray()
        ->and($resolved['value'] ?? null)->toBe('front_and_back')
        ->and($resolved['label'] ?? null)->toBe('Both Sides (Front and Back)');
});

test('it falls back to default print side when requested value is invalid', function () {
    $workspaceOptions = DesignProfileRegistry::getWorkspaceOptions('tshirt-classic');

    $resolved = DesignProfileRegistry::resolveSelectedPrintSide('invalid-choice', $workspaceOptions);

    expect($resolved)
        ->toBeArray()
        ->and($resolved['value'] ?? null)->toBe('front_only')
        ->and($resolved['label'] ?? null)->toBe('Front Side');
});
