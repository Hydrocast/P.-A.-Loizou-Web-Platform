<?php

use App\Support\DesignDocument;

test('it encodes a wrapped design document envelope', function () {
    $encoded = DesignDocument::encode('{"objects":[]}', [
        'shirt_color' => ['id' => 'black', 'label' => 'Black'],
        'print_sides' => ['value' => 'front_only', 'label' => 'Front Side'],
    ]);

    $decoded = json_decode($encoded, true);

    expect($decoded)
        ->toBeArray()
        ->and($decoded['schema_version'] ?? null)->toBe(1)
        ->and($decoded['canvas_json'] ?? null)->toBe('{"objects":[]}')
        ->and($decoded['customization']['shirt_color']['label'] ?? null)->toBe('Black');
});

test('it extracts canvas json from wrapped envelope', function () {
    $stored = json_encode([
        'schema_version' => 1,
        'canvas_json' => '{"objects":[{"type":"text"}]}',
        'customization' => [],
    ], JSON_UNESCAPED_SLASHES);

    expect(DesignDocument::extractCanvasJson($stored))->toBe('{"objects":[{"type":"text"}]}');
});

test('it returns raw value for legacy stored canvas json', function () {
    $legacy = '{"objects":[{"type":"image"}]}';

    expect(DesignDocument::extractCanvasJson($legacy))->toBe($legacy);
    expect(DesignDocument::extractCustomization($legacy))->toBe([]);
});

test('it extracts shirt color and print sides labels from customization metadata', function () {
    $stored = json_encode([
        'schema_version' => 1,
        'canvas_json' => '{"objects":[]}',
        'customization' => [
            'shirt_color' => ['id' => 'navy', 'label' => 'Navy'],
            'print_sides' => ['value' => 'front_and_back', 'label' => 'Both Sides (Front and Back)'],
        ],
    ], JSON_UNESCAPED_SLASHES);

    expect(DesignDocument::extractShirtColorLabel($stored))->toBe('Navy')
        ->and(DesignDocument::extractPrintSidesLabel($stored))->toBe('Both Sides (Front and Back)');
});

test('it extracts unique image sources from wrapped design documents', function () {
    $stored = json_encode([
        'schema_version' => 1,
        'canvas_json' => json_encode([
            'objects' => [
                ['type' => 'text', 'text' => 'Hello'],
                ['type' => 'image', 'src' => '/images/clipart/star.png'],
                ['type' => 'image', 'src' => '/images/clipart/star.png'],
                ['type' => 'image', 'src' => '/images/uploads/custom.png'],
            ],
        ], JSON_UNESCAPED_SLASHES),
        'customization' => [],
    ], JSON_UNESCAPED_SLASHES);

    expect(DesignDocument::extractImageSrcs($stored))->toBe([
        '/images/clipart/star.png',
        '/images/uploads/custom.png',
    ]);
});

test('it extracts image sources from legacy raw fabric json and handles invalid input', function () {
    $legacy = json_encode([
        'objects' => [
            ['type' => 'image', 'src' => '/images/clipart/heart.png'],
            ['type' => 'path', 'path' => 'M0 0 L10 10'],
        ],
    ], JSON_UNESCAPED_SLASHES);

    expect(DesignDocument::extractImageSrcs($legacy))->toBe([
        '/images/clipart/heart.png',
    ])->and(DesignDocument::extractImageSrcs(null))->toBe([])
        ->and(DesignDocument::extractImageSrcs('not-json'))->toBe([]);
});
