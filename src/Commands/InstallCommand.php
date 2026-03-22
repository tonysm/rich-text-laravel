<?php

namespace Tonysm\RichTextLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process as FacadesProcess;
use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Tonysm\RichTextLaravel\RichTextLaravelServiceProvider;

class InstallCommand extends Command
{
    const JS_TRIX_LIBS_IMPORT_PATTERN = '/import [\'\"](?:\.\/)?libs\/trix[\'\"];?/';

    const JS_LEXXY_LIBS_IMPORT_PATTERN = '/import [\'\"](?:\.\/)?libs\/lexxy[\'\"];?/';

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
        $this->updateConfigFile($editor);
        $this->runDatabaseMigrations();

        $this->newLine();
        $this->components->info('Rich Text Laravel was installed successfully.');

        return self::SUCCESS;
    }

    private function resolveEditor(): string
    {
        if ($editor = $this->option('editor')) {
            return strtolower($editor);
        }

        if (! $this->input->isInteractive()) {
            return 'trix';
        }

        return strtolower(
            $this->components->choice('Which editor do you want to install?', ['Trix', 'Lexxy'], 'Trix')
        );
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

    private function updateJsDependencies(string $editor): void
    {
        if ($this->usingImportmaps()) {
            $this->installJsDependenciesWithImportmaps($editor);
        } else {
            $this->updateJsDependenciesWithNpm($editor);
        }
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

    private function runningSail(): bool
    {
        return (file_exists(base_path('docker-compose.yml')) || file_exists(base_path('compose.yaml'))) && str_contains(file_get_contents(base_path('composer.json')), 'laravel/sail');
    }

    private function usingImportmaps(): bool
    {
        return File::exists(base_path('routes/importmap.php'));
    }

    private function jsDependencies(string $editor): array
    {
        return match ($editor) {
            'trix' => ['trix' => '^2.1.16'],
            'lexxy' => ['@37signals/lexxy' => '^0.8.5-beta'],
        };
    }

    private function updateJsDependenciesWithNpm(string $editor): void
    {
        static::updateNodePackages(fn ($packages): array => $this->jsDependencies($editor) + $packages);

        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }
    }

    private function runCommands(array $commands): void
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, string $line): void {
            $this->output->write('    '.$line);
        });
    }

    private function installJsDependenciesWithImportmaps(string $editor): void
    {
        if ($editor === 'lexxy') {
            File::append(base_path('routes/importmap.php'), <<<'PHP'
            Importmap::pin('@37signals/lexxy', to: '/vendor/rich-text-laravel/lexxy.esm.js');
            PHP);
        } else {
            File::append(base_path('routes/importmap.php'), <<<'PHP'
            Importmap::pin('trix', to: '/vendor/rich-text-laravel/trix.esm.js');
            PHP);
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
        $this->ensureTrixFieldComponentIsCopied();
        $this->updateAppLayoutFiles();
        $this->updateJsDependencies($editor);
    }

    private function installLexxyFrontend(string $editor): void
    {
        $this->ensureLexxyLibIsImported();
        $this->ensureLexxyFieldComponentIsCopied();
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

    private function ensureTrixFieldComponentIsCopied(): void
    {
        File::ensureDirectoryExists(resource_path('views/components'));

        File::copy(
            __DIR__.'/../../stubs/resources/views/components/trix-input.blade.php',
            resource_path('views/components/trix-input.blade.php'),
        );
    }

    private function ensureLexxyFieldComponentIsCopied(): void
    {
        File::ensureDirectoryExists(resource_path('views/components'));

        File::copy(
            __DIR__.'/../../stubs/resources/views/components/lexxy-input.blade.php',
            resource_path('views/components/lexxy-input.blade.php'),
        );
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
            "\n{$stylesTag}\n$1$2",
            $contents,
            1,
        );

        File::put($headFile, $updated);

        // If the pattern update wasn't enough, we simply append it...
        if (! str_contains($contents, '<x-rich-text::styles')) {
            File::append($headFile, "\n{$stylesTag}\n");
        }
    }

    private function stylesTag(): string
    {
        $theme = match (true) {
            $this->usingDaisyUi() => 'daisyui',
            default => $this->components->choice('Would you like to use a specific theme?', ['daisyui'], null),
        };

        $theme = $theme ? " theme=\"{$theme}\"" : '';

        return sprintf(
            '<x-rich-text::styles%s data-turbo-track="false" />',
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

    private function usingDaisyUiViaHotwireStarterKit(): bool
    {
        return file_exists(resource_path('css/app.css')) && str_contains(File::get(resource_path('css/app.css')), 'daisyui');
    }

    private function updateConfigFile(string $editor): void
    {
        if ($editor === 'trix') {
            return;
        }

        $configPath = config_path('rich-text-laravel.php');

        if (! File::exists($configPath)) {
            $this->publishConfigFile();
        }

        File::replaceInFile(
            "'editor' => env('RICH_TEXT_EDITOR', 'trix')",
            "'editor' => env('RICH_TEXT_EDITOR', '{$editor}')",
            $configPath,
        );
    }

    protected function publishConfigFile(): void
    {
        FacadesProcess::forever()->run([
            $this->phpBinary(),
            'artisan',
            'vendor:publish',
            '--tag',
            'rich-text-laravel-config',
            '--provider',
            RichTextLaravelServiceProvider::class,
        ], fn ($_type, $output) => $this->output->write($output));
    }

    /**
     * Update the "package.json" file.
     *
     * @param  bool  $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    private function phpBinary(): string
    {
        return (new PhpExecutableFinder)->find(false) ?: 'php';
    }
}
