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
            $this->choice('Which editor do you want to install?', ['Trix', 'Lexxy'], 'Trix')
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
        if (! $this->confirm('A new migration was published to your app. Do you want to run it now?', true)) {
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
        return file_exists(base_path('docker-compose.yml')) && str_contains(file_get_contents(base_path('composer.json')), 'laravel/sail');
    }

    private function usingImportmaps(): bool
    {
        return File::exists(base_path('routes/importmap.php'));
    }

    private function jsDependencies(string $editor): array
    {
        return match ($editor) {
            'trix' => ['trix' => '^2.0.10'],
            'lexxy' => ['@37signals/lexxy' => '^0.7.6-beta'],
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
        FacadesProcess::forever()->run(array_merge([
            $this->phpBinary(),
            'artisan',
            'importmap:pin',
        ], array_keys($this->jsDependencies($editor))), fn ($_type, $output) => $this->output->write($output));
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
        $this->updateAppLayoutFiles($editor);
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

    private function updateAppLayoutFiles(string $editor): void
    {
        $this->updateLayoutFiles($editor);
        $this->updateStarterKitHeadFiles($editor);
    }

    private function updateLayoutFiles(string $editor): void
    {
        $layouts = collect(['app', 'guest'])
            ->map(fn ($name) => resource_path("views/layouts/{$name}.blade.php"))
            ->filter(fn ($file) => File::exists($file));

        if ($layouts->isEmpty()) {
            return;
        }

        $stylesTag = $this->stylesTag($editor);

        $layouts->each(function ($file) use ($stylesTag): void {
            $contents = File::get($file);

            if (str_contains($contents, '<x-rich-text::styles')) {
                return;
            }

            File::put($file, preg_replace('/(\s*)(<\/head>)/', "\\1    {$stylesTag}\\1\\2", $contents));
        });
    }

    private function updateStarterKitHeadFiles(string $editor): void
    {
        $headFile = resource_path('views/partials/head.blade.php');

        if (! File::exists($headFile)) {
            return;
        }

        $contents = File::get($headFile);

        if (str_contains($contents, '<x-rich-text::styles')) {
            return;
        }

        $stylesTag = $this->stylesTag($editor);

        File::append($headFile, "\n{$stylesTag}\n");
    }

    private function stylesTag(string $editor): string
    {
        return match ($editor) {
            'trix' => '<x-rich-text::styles theme="richtextlaravel" data-turbo-track="false" />',
            'lexxy' => '<x-rich-text::styles />',
        };
    }

    private function updateConfigFile(string $editor): void
    {
        if ($editor === 'trix') {
            return;
        }

        $configPath = config_path('rich-text-laravel.php');

        if (! File::exists($configPath)) {
            return;
        }

        File::replaceInFile(
            "'editor' => env('RICH_TEXT_EDITOR', 'trix')",
            "'editor' => env('RICH_TEXT_EDITOR', '{$editor}')",
            $configPath,
        );
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
