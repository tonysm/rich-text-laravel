<?php

namespace Tonysm\RichTextLaravel\Commands\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process as FacadesProcess;
use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Tonysm\RichTextLaravel\RichTextLaravelServiceProvider;

trait InteractsWithInstallation
{
    const JS_TRIX_LIBS_IMPORT_PATTERN = '/import [\'\"](?:\.\/)?libs\/trix[\'\"];?/';

    const JS_LEXXY_LIBS_IMPORT_PATTERN = '/import [\'\"](?:\.\/)?libs\/lexxy[\'\"];?/';

    private function resolveEditor(): string
    {
        if ($editor = $this->option('editor')) {
            return strtolower($editor);
        }

        if (! $this->input->isInteractive()) {
            return 'trix';
        }

        return strtolower(
            $this->components->choice('Which editor do you want to use?', ['Trix', 'Lexxy'], 'Trix')
        );
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

    private function jsDependencyKey(string $editor): string
    {
        return match ($editor) {
            'trix' => 'trix',
            'lexxy' => '@37signals/lexxy',
        };
    }

    private function updateJsDependencies(string $editor): void
    {
        if ($this->usingImportmaps()) {
            $this->installJsDependenciesWithImportmaps($editor);
        } else {
            $this->updateJsDependenciesWithNpm($editor);
        }
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

    private function runningSail(): bool
    {
        return (file_exists(base_path('docker-compose.yml')) || file_exists(base_path('compose.yaml'))) && str_contains(file_get_contents(base_path('composer.json')), 'laravel/sail');
    }

    private function publishConfigFile(): void
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

    private function updateConfigFile(string $oldEditor, string $newEditor): void
    {
        $configPath = config_path('rich-text-laravel.php');

        if (! File::exists($configPath)) {
            $this->publishConfigFile();
        }

        File::replaceInFile(
            "'editor' => env('RICH_TEXT_EDITOR', '{$oldEditor}')",
            "'editor' => env('RICH_TEXT_EDITOR', '{$newEditor}')",
            $configPath,
        );
    }

    private function phpBinary(): string
    {
        return (new PhpExecutableFinder)->find(false) ?: 'php';
    }

    private function importmapPin(string $editor): string
    {
        return match ($editor) {
            'trix' => "Importmap::pin('trix', to: '/vendor/rich-text-laravel/trix.esm.js');",
            'lexxy' => "Importmap::pin('@37signals/lexxy', to: '/vendor/rich-text-laravel/lexxy.esm.js');",
        };
    }

    private function jsLibsImportPattern(string $editor): string
    {
        return match ($editor) {
            'trix' => self::JS_TRIX_LIBS_IMPORT_PATTERN,
            'lexxy' => self::JS_LEXXY_LIBS_IMPORT_PATTERN,
        };
    }
}
