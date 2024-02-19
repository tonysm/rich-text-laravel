<?php

namespace Tonysm\RichTextLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tonysm\RichTextLaravel\RichTextLaravelServiceProvider;

class InstallCommand extends Command
{
    const JS_BOOTSTRAP_IMPORT_PATTERN = '/(.*[\'\"](?:\.\/)?bootstrap[\'\"].*)/';

    const JS_TRIX_LIBS_IMPORT_PATTERN = '/import [\'\"](?:\.\/)?libs\/trix[\'\"];?/';

    public $signature = 'richtext:install
        {--no-model : Skip publishing the RichText model files.}
    ';

    public $description = 'Installs the package.';

    public function handle()
    {
        $this->components->info('Installing Rich Text Laravel.');

        if (! $this->option('no-model')) {
            $this->publishMigration();
        }

        $this->ensureTrixLibIsImported();
        $this->ensureTrixOverridesStylesIsPublished();
        $this->ensureTrixFieldComponentIsCopied();
        $this->updateAppLayoutFiles();
        $this->updateJsDependencies();

        $this->line('');
        $this->components->info('Rich Text Laravel installed successfully.');

        return self::SUCCESS;
    }

    private function publishMigration()
    {
        $this->components->info('Publishing migrations.');

        $this->callSilently('vendor:publish', [
            '--tag' => 'rich-text-laravel-migrations',
            '--provider' => RichTextLaravelServiceProvider::class,
        ]);
    }

    private function updateJsDependencies()
    {
        if ($this->usingImportmaps()) {
            $this->installJsDependenciesWithImportmaps();
        } else {
            $this->updateJsDependenciesWithNpm();
        }
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
        $this->components->info('Installing JS dependencies (Node).');

        $this->updateNodePackages(function ($packages) {
            return $this->jsDependencies() + $packages;
        });

        $this->components->info('Installing and building Node dependencies.');

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
        $this->components->info('Installing JS dependencies (Importmaps).');

        $this->callSilent('importmap:pin '.implode(' ', array_keys($this->jsDependencies())));
    }

    private function ensureTrixLibIsImported(): void
    {
        $this->components->info('Publishing resources/js/libs/trix.js module.');

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

        if (! File::exists($entrypoint)) {
            $this->components->warn(sprintf('Add `%s` your main JS file.', sprintf("\nimport '%slibs/trix';\n", $this->usingImportmaps() ? '' : './')));

            return;
        }

        $this->components->info(sprintf('Importing the `libs/trix.js` module in `%s`', str($entrypoint)->after(resource_path())));

        // If the import line doesn't exist on the js/app.js file, add it after the import
        // of the bootstrap.js file that ships with Laravel's default scaffolding.

        if (! preg_match(self::JS_TRIX_LIBS_IMPORT_PATTERN, File::get($entrypoint))) {
            File::put($entrypoint, preg_replace(
                self::JS_BOOTSTRAP_IMPORT_PATTERN,
                str_replace(
                    '%path%',
                    $this->usingImportmaps() ? '' : './',
                    <<<'JS'
                    \1
                    import '%path%libs/trix';
                    JS,
                ),
                File::get($entrypoint),
            ));
        }
    }

    private function ensureTrixOverridesStylesIsPublished(): void
    {
        $this->components->info('Publishing styles.');
        File::copy(__DIR__.'/../../stubs/resources/css/trix.css', resource_path('css/_trix.css'));

        if (File::exists($mainCssFile = resource_path('css/app.css')) && ! str_contains(File::get($mainCssFile), '_trix.css')) {
            $this->components->info('Importing the `resources/css/_trix.css` styles file.');

            File::prepend($mainCssFile, "@import './_trix.css';\n");
        } else {
            $this->components->warn('Import the `resources/css/_trix.css` in your main CSS file.');
        }
    }

    private function ensureTrixFieldComponentIsCopied(): void
    {
        $this->components->info('Publishing the `<x-trix-input />` Blade component.');

        File::ensureDirectoryExists(resource_path('views/components'));

        File::copy(
            __DIR__.'/../../stubs/resources/views/components/trix-input.blade.php',
            resource_path('views/components/trix-input.blade.php'),
        );
    }

    private function updateAppLayoutFiles(): void
    {
        $this->components->info('Updating layouts.');

        $this->existingLayoutFiles()
            ->each(function ($file) {
                $contents = File::get($file);

                if (str_contains($contents, '<x-rich-text::styles />')) {
                    return;
                }

                File::put($file, preg_replace('/(\s*)(<\/head>)/', "\\1    <x-rich-text::styles breeze data-turbo-track=\"false\" />\n\\1\\2", $contents));
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
}
