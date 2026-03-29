<?php

namespace App\Support;

/**
 * Handles encoding and decoding the stored design document format.
 *
 * Backward compatibility:
 * - Old records may store raw Fabric JSON directly.
 * - New records store an envelope containing canvas_json and customization metadata.
 */
class DesignDocument
{
    private static array $decodeCache = [];

    private static function decodeSafely(?string $storedValue): mixed
    {
        if (! is_string($storedValue) || trim($storedValue) === '') {
            return null;
        }

        $cacheKey = md5($storedValue);

        if (array_key_exists($cacheKey, self::$decodeCache)) {
            return self::$decodeCache[$cacheKey];
        }

        $decoded = json_decode($storedValue, true);
        self::$decodeCache[$cacheKey] = $decoded;

        return $decoded;
    }

    /**
     * Encode a stored design document string.
     */
    public static function encode(string $canvasJson, array $customization = []): string
    {
        return json_encode([
            'schema_version' => 1,
            'canvas_json' => $canvasJson,
            'customization' => $customization,
        ], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Extract the Fabric canvas JSON from a stored design value.
     *
     * Supports:
     * - old raw Fabric JSON
     * - new envelope format
     */
    public static function extractCanvasJson(?string $storedValue): ?string
    {
        if (! is_string($storedValue) || trim($storedValue) === '') {
            return null;
        }

        $decoded = self::decodeSafely($storedValue);

        if (
            is_array($decoded) &&
            array_key_exists('schema_version', $decoded) &&
            array_key_exists('canvas_json', $decoded) &&
            is_string($decoded['canvas_json'])
        ) {
            return $decoded['canvas_json'];
        }

        return $storedValue;
    }

    /**
     * Extract customization metadata from a stored design value.
     */
    public static function extractCustomization(?string $storedValue): array
    {
        if (! is_string($storedValue) || trim($storedValue) === '') {
            return [];
        }

        $decoded = self::decodeSafely($storedValue);

        if (
            is_array($decoded) &&
            array_key_exists('schema_version', $decoded) &&
            isset($decoded['customization']) &&
            is_array($decoded['customization'])
        ) {
            return $decoded['customization'];
        }

        return [];
    }

    public static function extractShirtColorLabel(?string $storedValue): ?string
    {
        $customization = self::extractCustomization($storedValue);
        $label = $customization['shirt_color']['label'] ?? null;

        return is_string($label) && trim($label) !== '' ? $label : null;
    }

    public static function extractPrintSidesLabel(?string $storedValue): ?string
    {
        $customization = self::extractCustomization($storedValue);
        $label = $customization['print_sides']['label'] ?? null;

        return is_string($label) && trim($label) !== '' ? $label : null;
    }

    public static function extractSizeLabel(?string $storedValue): ?string
    {
        $customization = self::extractCustomization($storedValue);
        $label = $customization['size']['label'] ?? null;

        return is_string($label) && trim($label) !== '' ? $label : null;
    }

    /**
     * Extract image sources from Fabric JSON in a stored design value.
     *
     * @return array<int, string>
     */
    public static function extractImageSrcs(?string $storedValue): array
    {
        $canvasJson = self::extractCanvasJson($storedValue);

        if (! is_string($canvasJson) || trim($canvasJson) === '') {
            return [];
        }

        $decoded = self::decodeSafely($canvasJson);

        if (! is_array($decoded)) {
            return [];
        }

        $objects = $decoded['objects'] ?? null;

        if (! is_array($objects)) {
            return [];
        }

        $imageSources = [];

        foreach ($objects as $object) {
            if (! is_array($object)) {
                continue;
            }

            if (($object['type'] ?? null) !== 'image') {
                continue;
            }

            $src = $object['src'] ?? null;

            if (! is_string($src) || trim($src) === '') {
                continue;
            }

            $imageSources[] = trim($src);
        }

        return array_values(array_unique($imageSources));
    }
}
