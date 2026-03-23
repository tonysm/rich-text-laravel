<?php

namespace Tonysm\RichTextLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process as FacadesProcess;
use Tonysm\RichTextLaravel\Commands\Concerns\InteractsWithInstallation;
use Tonysm\RichTextLaravel\RichTextLaravelServiceProvider;

class InstallCommand extends Command
{
    use InteractsWithInstallation;

    public $signature = 'richtext:install
        {--no-model : Skip publishing the RichText model files.}
        {--editor= : The editor to install (trix or lexxy).}
    ';

    public $description = 'Installs the package.';

    public function handle(): int
    {
        $editor = $this->resolveEditor();

        if (! $this->option('no-model')) {
            $this->publishMigration();
        }

        $this->publishAssets();
        $this->installEditorFrontend($editor);
        $this->updateConfigFile('trix', $editor);
        $this->runDatabaseMigrations();

        $this->newLine();
        $this->components->info('Rich Text Laravel was installed successfully.');

        return self::SUCCESS;
    }

    private function publishMigration(): void
    {
        FacadesProcess::forever()->run([
            $this->phpBinary(),
            'artisan',
            'vendor:publish',
            '--tag',
            'rich-text-laravel-migrations',
            '--provider',
            RichTextLaravelServiceProvider::class,
        ], fn ($_type, $output) => $this->output->write($output));
    }

    private function publishAssets(): void
    {
        FacadesProcess::forever()->run([
            $this->phpBinary(),
            'artisan',
            'vendor:publish',
            '--tag',
            'rich-text-laravel-assets',
            '--provider',
            RichTextLaravelServiceProvider::class,
        ], fn ($_type, $output) => $this->output->write($output));
    }

    private function runDatabaseMigrations(): void
    {
        if (! $this->components->confirm('A new migration was published to your app. Do you want to run it now?', true)) {
            return;
        }

        if ($this->runningSail() && ! env('LARAVEL_SAIL')) {
            FacadesProcess::forever()->run([
                './vendor/bin/sail',
                'artisan',
                'migrate',
            ], fn ($_type, $output) => $this->output->write($output));
        } else {
            FacadesProcess::forever()->run([
                $this->phpBinary(),
                'artisan',
                'migrate',
            ], fn ($_type, $output) => $this->output->write($output));
        }
    }

    private function installEditorFrontend(string $editor): void
    {
        match ($editor) {
            'trix' => $this->installTrixFrontend($editor),
            'lexxy' => $this->installLexxyFrontend($editor),
        };
    }

    private function installTrixFrontend(string $editor): void
    {
        $this->ensureTrixLibIsImported();
        $this->updateAppLayoutFiles();
        $this->updateJsDependencies($editor);
    }

    private function installLexxyFrontend(string $editor): void
    {
        $this->ensureLexxyLibIsImported();
        $this->updateAppLayoutFiles($editor);
        $this->updateJsDependencies($editor);
    }

    private function ensureTrixLibIsImported(): void
    {
        $trixRelativeDestinationPath = 'resources/js/libs/trix.js';

        $trixAbsoluteDestinationPath = base_path($trixRelativeDestinationPath);

        if (File::exists($trixAbsoluteDestinationPath)) {
            $this->components->warn("File {$trixRelativeDestinationPath} already exists.");
        } else {
            File::ensureDirectoryExists(dirname($trixAbsoluteDestinationPath), recursive: true);
            File::copy(__DIR__.'/../../stubs/resources/js/trix.js', $trixAbsoluteDestinationPath);
        }

        $entrypoint = Arr::first([
            resource_path('js/libs/index.js'),
            resource_path('js/app.js'),
        ], fn ($file): bool => file_exists($file));

        if (! $entrypoint) {
            return;
        }

        if (preg_match(self::JS_TRIX_LIBS_IMPORT_PATTERN, File::get($entrypoint))) {
            return;
        }

        File::prepend($entrypoint, str_replace('%path%', $this->usingImportmaps() ? '' : './', <<<'JS'
        import "%path%libs/trix";

        JS));
    }

    private function ensureLexxyLibIsImported(): void
    {
        $lexxyRelativeDestinationPath = 'resources/js/libs/lexxy.js';

        $lexxyAbsoluteDestinationPath = base_path($lexxyRelativeDestinationPath);

        if (File::exists($lexxyAbsoluteDestinationPath)) {
            $this->components->warn("File {$lexxyRelativeDestinationPath} already exists.");
        } else {
            File::ensureDirectoryExists(dirname($lexxyAbsoluteDestinationPath), recursive: true);
            File::copy(__DIR__.'/../../stubs/resources/js/lexxy.js', $lexxyAbsoluteDestinationPath);
        }

        $entrypoint = Arr::first([
            resource_path('js/libs/index.js'),
            resource_path('js/app.js'),
        ], fn ($file): bool => file_exists($file));

        if (! $entrypoint) {
            return;
        }

        if (preg_match(self::JS_LEXXY_LIBS_IMPORT_PATTERN, File::get($entrypoint))) {
            return;
        }

        File::prepend($entrypoint, str_replace('%path%', $this->usingImportmaps() ? '' : './', <<<'JS'
        import "%path%libs/lexxy";

        JS));
    }

    private function updateAppLayoutFiles(): void
    {
        $this->updateLayoutFiles();
        $this->updateStarterKitHeadFiles();
    }

    private function updateLayoutFiles(): void
    {
        $layouts = collect(['app', 'guest'])
            ->map(fn ($name) => resource_path("views/layouts/{$name}.blade.php"))
            ->filter(fn ($file) => File::exists($file));

        if ($layouts->isEmpty()) {
            return;
        }

        $stylesTag = $this->stylesTag();

        $layouts->each(function ($file) use ($stylesTag): void {
            $contents = File::get($file);

            if (str_contains($contents, '<x-rich-text::styles')) {
                return;
            }

            File::put($file, preg_replace('/(\s*)(<\/head>)/', "\\1    {$stylesTag}\\1\\2", $contents));
        });
    }

    private function updateStarterKitHeadFiles(): void
    {
        $headFile = resource_path('views/partials/head.blade.php');

        if (! File::exists($headFile)) {
            return;
        }

        $contents = File::get($headFile);

        if (str_contains($contents, '<x-rich-text::styles')) {
            return;
        }

        $stylesTag = $this->stylesTag();

        $updated = preg_replace(
            '/(\s*)(@vite\b|<link[^>]*tailwindcss)/',
            "\n\n{$stylesTag}$1$2",
            $contents,
            1,
        );

        File::put($headFile, $updated);

        // If the pattern update wasn't enough, we simply append it...
        if (! str_contains(File::get($headFile), '<x-rich-text::styles')) {
            File::append($headFile, "\n{$stylesTag}\n");
        }
    }

    private function stylesTag(): string
    {
        $theme = match (true) {
            $this->usingDaisyUi() => 'daisyui',
            $this->usingFlux() => 'flux',
            default => $this->components->choice('Would you like to use a specific theme?', ['daisyui', 'flux'], null),
        };

        return sprintf(
            '<x-rich-text::styles%s data-turbo-track="reload" />',
            $theme ? " theme=\"{$theme}\"" : '',
        );
    }

    private function usingDaisyUi(): bool
    {
        return $this->usingDaisyUiViaNpm() || $this->usingDaisyUiViaHotwireStarterKit();
    }

    private function usingDaisyUiViaNpm(): bool
    {
        if (! file_exists(base_path('package.json'))) {
            return false;
        }

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        return Arr::has($packages, 'dependencies.daisyui') || Arr::has($packages, 'devDependencies.daisyui');
    }

    private function usingFlux(): bool
    {
        return file_exists(base_path('composer.json')) && str_contains(File::get(base_path('composer.json')), 'livewire/flux');
    }

    private function usingDaisyUiViaHotwireStarterKit(): bool
    {
        return file_exists(resource_path('css/app.css')) && str_contains(File::get(resource_path('css/app.css')), 'daisyui');
    }
}
