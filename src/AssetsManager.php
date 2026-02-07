<?php

namespace Tonysm\RichTextLaravel;

use RuntimeException;

class AssetsManager
{
    protected ?array $manifestContent = null;

    public function url(string $asset): string
    {
        $this->loadManifestIfNotLoaded();

        $path = $this->manifestContent[$asset] ?? null;

        if (\is_null($path)) {
            throw new RuntimeException("Asset '{$asset}' not found in manifest.");
        }

        return asset($path);
    }

    private function loadManifestIfNotLoaded(): void
    {
        if ($this->manifestContent !== null) {
            return;
        }

        $manifestPath = public_path('vendor/rich-text-laravel/manifest.json');

        if (! file_exists($manifestPath)) {
            throw new RuntimeException('Assets manifest not found. Please run "php artisan vendor:publish --tag=rich-text-laravel-assets" to publish the assets.');
        }

        $this->manifestContent = json_decode(file_get_contents($manifestPath), true);
    }
}
