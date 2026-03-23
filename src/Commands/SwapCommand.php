<?php

namespace Tonysm\RichTextLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Tonysm\RichTextLaravel\Commands\Concerns\InteractsWithInstallation;

class SwapCommand extends Command
{
    use InteractsWithInstallation;

    public $signature = 'richtext:swap
        {--editor= : The editor to swap to (trix or lexxy).}
    ';

    public $description = 'Swap between rich text editors.';

    public function handle(): int
    {
        $newEditor = $this->resolveEditor();
        $oldEditor = config('rich-text-laravel.editor', 'trix');

        if ($oldEditor === $newEditor) {
            $this->components->warn("Already using {$newEditor}.");

            return self::SUCCESS;
        }

        $this->swapJsLibFile($oldEditor, $newEditor);
        $this->swapImportInEntrypoint($oldEditor, $newEditor);
        $this->swapJsDependencies($oldEditor, $newEditor);
        $this->publishConfigFile();
        $this->updateConfigFile($oldEditor, $newEditor);

        $this->newLine();
        $this->components->info("Swapped from {$oldEditor} to {$newEditor} successfully.");

        return self::SUCCESS;
    }

    private function swapJsLibFile(string $oldEditor, string $newEditor): void
    {
        $oldLibPath = base_path("resources/js/libs/{$oldEditor}.js");

        if (File::exists($oldLibPath)) {
            $stubPath = __DIR__."/../../stubs/resources/js/{$oldEditor}.js";
            $stubContents = File::get($stubPath);
            $currentContents = File::get($oldLibPath);

            if ($currentContents !== $stubContents) {
                $this->components->warn("File resources/js/libs/{$oldEditor}.js has custom changes and was not deleted.");
            } else {
                File::delete($oldLibPath);
            }
        }

        $newLibPath = base_path("resources/js/libs/{$newEditor}.js");

        File::ensureDirectoryExists(dirname($newLibPath), recursive: true);
        File::copy(__DIR__."/../../stubs/resources/js/{$newEditor}.js", $newLibPath);
    }

    private function swapImportInEntrypoint(string $oldEditor, string $newEditor): void
    {
        $entrypoint = Arr::first([
            resource_path('js/libs/index.js'),
            resource_path('js/app.js'),
        ], fn ($file): bool => file_exists($file));

        if (! $entrypoint) {
            return;
        }

        $contents = File::get($entrypoint);
        $prefix = $this->usingImportmaps() ? '' : './';
        $newImport = "import \"{$prefix}libs/{$newEditor}\";";

        $updated = preg_replace($this->jsLibsImportPattern($oldEditor), $newImport, $contents, 1, $count);

        if ($count > 0) {
            File::put($entrypoint, $updated);
        } else {
            File::prepend($entrypoint, $newImport."\n");
        }
    }

    private function swapJsDependencies(string $oldEditor, string $newEditor): void
    {
        if ($this->usingImportmaps()) {
            $this->swapImportmapPins($oldEditor, $newEditor);
        } else {
            $this->swapNpmDependencies($oldEditor, $newEditor);
        }
    }

    private function swapNpmDependencies(string $oldEditor, string $newEditor): void
    {
        $oldDependencyKey = $this->jsDependencyKey($oldEditor);

        static::updateNodePackages(function ($packages) use ($oldDependencyKey, $newEditor): array {
            unset($packages[$oldDependencyKey]);

            return $this->jsDependencies($newEditor) + $packages;
        });

        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }
    }

    private function swapImportmapPins(string $oldEditor, string $newEditor): void
    {
        $importmapPath = base_path('routes/importmap.php');

        if (File::exists($importmapPath)) {
            $contents = File::get($importmapPath);
            $contents = str_replace($this->importmapPin($oldEditor), '', $contents);
            File::put($importmapPath, rtrim($contents)."\n");
        }

        $this->installJsDependenciesWithImportmaps($newEditor);
    }
}
