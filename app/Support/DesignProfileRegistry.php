<?php

namespace App\Support;

/**
 * Central registry for shared reusable designer profiles.
 *
 * This class provides one consistent place to:
 * - resolve profiles from config/design_profiles.php
 * - normalize frontend colour option payloads
 * - safely resolve template configuration
 * - safely resolve selected colour fallbacks
 *
 * Invalid or incomplete profile data never throws hard failures here.
 * Instead, unsafe entries are ignored so controllers can fail gracefully.
 */
class DesignProfileRegistry
{
    private static array $profileCache = [];

    /**
     * Returns the raw profile array for the given key, or null when missing/invalid.
     */
    public static function getProfile(?string $profileKey): ?array
    {
        if (! is_string($profileKey) || trim($profileKey) === '') {
            return null;
        }

        if (array_key_exists($profileKey, self::$profileCache)) {
            return self::$profileCache[$profileKey];
        }

        $profiles = config('design_profiles', []);
        $profile = $profiles[$profileKey] ?? null;

        $resolved = is_array($profile) ? $profile : null;
        self::$profileCache[$profileKey] = $resolved;

        return $resolved;
    }

    /**
     * Returns the resolved template configuration for a given profile key.
     */
    public static function getTemplateConfig(?string $profileKey): ?array
    {
        $profile = self::getProfile($profileKey);

        if (! $profile) {
            return null;
        }

        $templateConfig = $profile['template_config'] ?? null;

        return is_array($templateConfig) ? $templateConfig : null;
    }

    /**
     * Returns structured product detail configuration for a given profile key.
     */
    public static function getProductDetails(?string $profileKey): ?array
    {
        $profile = self::getProfile($profileKey);

        if (! $profile) {
            return null;
        }

        $productDetails = $profile['product_details'] ?? null;

        return is_array($productDetails) ? $productDetails : null;
    }

    /**
     * Returns Product Detail size options for a given profile key.
     */
    public static function getSizeOptions(?string $profileKey): array
    {
        $profile = self::getProfile($profileKey);

        if (! $profile) {
            return [];
        }

        $sizeOptions = $profile['size_options'] ?? null;

        if (
            ! is_array($sizeOptions) ||
            ! ($sizeOptions['enabled'] ?? false) ||
            ! isset($sizeOptions['choices']) ||
            ! is_array($sizeOptions['choices'])
        ) {
            return [];
        }

        return collect($sizeOptions['choices'])
            ->filter(fn ($choice) => is_array($choice))
            ->map(fn (array $choice) => [
                'value' => self::stringOrEmpty($choice['value'] ?? null),
                'label' => self::stringOrEmpty($choice['label'] ?? null),
            ])
            ->filter(fn (array $choice) => $choice['value'] !== '' && $choice['label'] !== '')
            ->values()
            ->all();
    }

    /**
     * Resolves the selected size safely from request/query or profile default.
     */
    public static function resolveSelectedSize(
        mixed $requestedSize,
        ?string $profileKey,
    ): ?array {
        $choices = collect(self::getSizeOptions($profileKey));

        if ($choices->isEmpty()) {
            return null;
        }

        if (is_string($requestedSize) && trim($requestedSize) !== '') {
            $matched = $choices->firstWhere('value', trim($requestedSize));

            if (is_array($matched)) {
                return $matched;
            }
        }

        $profile = self::getProfile($profileKey);
        $defaultValue = $profile['size_options']['default'] ?? null;

        if (is_string($defaultValue) && trim($defaultValue) !== '') {
            $matched = $choices->firstWhere('value', trim($defaultValue));

            if (is_array($matched)) {
                return $matched;
            }
        }

        return $choices->first();
    }

    /**
     * Returns Product Detail colour options in snake_case form.
     */
    public static function getProductDetailColorOptions(?string $profileKey): array
    {
        $profile = self::getProfile($profileKey);

        if (! $profile) {
            return [];
        }

        $colors = $profile['colors'] ?? null;

        if (! is_array($colors)) {
            return [];
        }

        return collect($colors)
            ->filter(fn ($color) => is_array($color))
            ->map(function (array $color) {
                return [
                    'id' => self::stringOrEmpty($color['id'] ?? null),
                    'label' => self::stringOrEmpty($color['label'] ?? null),
                    'swatch_hex' => self::stringOrDefault($color['swatch_hex'] ?? null, '#000000'),
                    'image_url' => self::nullableString($color['mockup_image_url'] ?? null),
                    'thumbnail_url' => self::nullableString($color['thumbnail_image_url'] ?? null),
                ];
            })
            ->filter(fn (array $color) => $color['id'] !== '' && $color['label'] !== '' && $color['image_url'] !== null)
            ->values()
            ->all();
    }

    /**
     * Returns Design Workspace colour options in camelCase form.
     */
    public static function getWorkspaceColorOptions(?string $profileKey): array
    {
        $profile = self::getProfile($profileKey);

        if (! $profile) {
            return [];
        }

        $colors = $profile['colors'] ?? null;

        if (! is_array($colors)) {
            return [];
        }

        return collect($colors)
            ->filter(fn ($color) => is_array($color))
            ->map(function (array $color) {
                return [
                    'id' => self::stringOrEmpty($color['id'] ?? null),
                    'label' => self::stringOrEmpty($color['label'] ?? null),
                    'swatchHex' => self::stringOrDefault($color['swatch_hex'] ?? null, '#000000'),
                    'mockupImageUrl' => self::nullableString($color['mockup_image_url'] ?? null),
                    'thumbnailImageUrl' => self::nullableString($color['thumbnail_image_url'] ?? null),
                ];
            })
            ->filter(fn (array $color) => $color['id'] !== '' && $color['label'] !== '' && $color['mockupImageUrl'] !== null)
            ->values()
            ->all();
    }

    /**
     * Returns workspace option configuration for a given profile key.
     */
    public static function getWorkspaceOptions(?string $profileKey): array
    {
        $profile = self::getProfile($profileKey);

        if (! $profile) {
            return [];
        }

        $workspaceOptions = $profile['workspace_options'] ?? null;

        return is_array($workspaceOptions) ? $workspaceOptions : [];
    }

    /**
     * Resolve the selected print side safely for the workspace.
     */
    public static function resolveSelectedPrintSide(
        mixed $requestedPrintSide,
        array $workspaceOptions,
    ): ?array {
        $printSides = $workspaceOptions['print_sides'] ?? null;

        if (
            ! is_array($printSides) ||
            ! ($printSides['enabled'] ?? false) ||
            ! isset($printSides['choices']) ||
            ! is_array($printSides['choices'])
        ) {
            return null;
        }

        $choices = collect($printSides['choices'])
            ->filter(fn ($choice) => is_array($choice))
            ->map(fn (array $choice) => [
                'value' => self::stringOrEmpty($choice['value'] ?? null),
                'label' => self::stringOrEmpty($choice['label'] ?? null),
            ])
            ->filter(fn (array $choice) => $choice['value'] !== '' && $choice['label'] !== '')
            ->values();

        if ($choices->isEmpty()) {
            return null;
        }

        if (is_string($requestedPrintSide) && $requestedPrintSide !== '') {
            $matched = $choices->firstWhere('value', $requestedPrintSide);

            if (is_array($matched)) {
                return $matched;
            }
        }

        $defaultValue = $printSides['default'] ?? null;

        if (is_string($defaultValue) && $defaultValue !== '') {
            $matched = $choices->firstWhere('value', $defaultValue);

            if (is_array($matched)) {
                return $matched;
            }
        }

        return $choices->first();
    }

    /**
     * Resolves the active workspace colour id safely.
     *
     * Priority:
     * 1. valid requested colour
     * 2. valid default profile colour
     * 3. first available colour
     */
    public static function resolveSelectedColorId(
        mixed $requestedColor,
        ?string $profileKey,
        array $workspaceColorOptions,
    ): ?string {
        $availableColorIds = collect($workspaceColorOptions)
            ->pluck('id')
            ->filter(fn ($id) => is_string($id) && $id !== '')
            ->values();

        if (
            is_string($requestedColor) &&
            $requestedColor !== '' &&
            $availableColorIds->contains($requestedColor)
        ) {
            return $requestedColor;
        }

        $profile = self::getProfile($profileKey);
        $defaultColorKey = $profile['default_color_key'] ?? null;

        if (
            is_string($defaultColorKey) &&
            $defaultColorKey !== '' &&
            $availableColorIds->contains($defaultColorKey)
        ) {
            return $defaultColorKey;
        }

        return $availableColorIds->first();
    }

    /**
     * Returns true when a profile key exists and resolves to a valid profile array.
     */
    public static function hasProfile(?string $profileKey): bool
    {
        return self::getProfile($profileKey) !== null;
    }

    private static function stringOrEmpty(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private static function stringOrDefault(mixed $value, string $default): string
    {
        return is_string($value) && trim($value) !== '' ? $value : $default;
    }

    private static function nullableString(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? $value : null;
    }
}
