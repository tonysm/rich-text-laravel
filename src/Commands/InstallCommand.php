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

    public $signature = 'richtext:install
        {--no-model : Skip publishing the RichText model files.}
    ';

    public $description = 'Installs the package.';

    public function handle()
    {
        if (! $this->option('no-model')) {
            $this->publishMigration();
        }

        $this->ensureTrixLibIsImported();
        $this->ensureTrixFieldComponentIsCopied();
        $this->updateAppLayoutFiles();
        $this->updateJsDependencies();
        $this->runDatabaseMigrations();

        $this->newLine();
        $this->components->info('Rich Text Laravel was installed successfully.');

        return self::SUCCESS;
    }

    private function publishMigration()
    {
        FacadesProcess::forever()->run([
            $this->phpBinary(),
            'artisan',
            'vendor:publish',
            '--tag', 'rich-text-laravel-migrations',
            '--provider', RichTextLaravelServiceProvider::class,
        ], fn ($_type, $output) => $this->output->write($output));
    }

    private function updateJsDependencies()
    {
        if ($this->usingImportmaps()) {
            $this->installJsDependenciesWithImportmaps();
        } else {
            $this->updateJsDependenciesWithNpm();
        }
    }

    private function runDatabaseMigrations()
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

    private function jsDependencies(): array
    {
        return [
            'trix' => '^2.0.10',
        ];
    }

    private function updateJsDependenciesWithNpm(): void
    {
        $this->updateNodePackages(function ($packages) {
            return $this->jsDependencies() + $packages;
        });

        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }
    }

    private function runCommands($commands)
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });
    }

    private function installJsDependenciesWithImportmaps(): void
    {
        FacadesProcess::forever()->run(array_merge([
            $this->phpBinary(),
            'artisan',
            'importmap:pin',
        ], array_keys($this->jsDependencies())), fn ($_type, $output) => $this->output->write($output));
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
        ], fn ($file) => file_exists($file));

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

    private function ensureTrixFieldComponentIsCopied(): void
    {
        File::ensureDirectoryExists(resource_path('views/components'));

        File::copy(
            __DIR__.'/../../stubs/resources/views/components/trix-input.blade.php',
            resource_path('views/components/trix-input.blade.php'),
        );
    }

    private function updateAppLayoutFiles(): void
    {
        $layouts = $this->existingLayoutFiles();

        if ($layouts->isEmpty()) {
            return;
        }

        $layouts->each(function ($file) {
            $contents = File::get($file);

            if (str_contains($contents, '<x-rich-text::styles')) {
                return;
            }

            File::put($file, preg_replace('/(\s*)(<\/head>)/', '\\1    <x-rich-text::styles theme="richtextlaravel" data-turbo-track="false" />\\1\\2', $contents));
        });
    }

    private function existingLayoutFiles()
    {
        return collect(['app', 'guest'])
            ->map(fn ($name) => resource_path("views/layouts/{$name}.blade.php"))
            ->filter(fn ($file) => File::exists($file));
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

    private function phpBinary()
    {
        return (new PhpExecutableFinder())->find(false) ?: 'php';
    }
}
