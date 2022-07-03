<?php

namespace Tonysm\RichTextLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Terminal;
use Tonysm\RichTextLaravel\RichTextLaravelServiceProvider;

class RichTextLaravelInstallCommand extends Command
{
    const JS_BOOTSTRAP_IMPORT_PATTERN = '/(.*[\'\"](?:\.\/)?bootstrap[\'\"].*)/';
    const JS_TRIX_LIBS_IMPORT_PATTERN = '/import [\'\"](?:\.\/)?libs\/trix[\'\"];?/';

    public $signature = 'richtext:install
        {--no-model : Skip publishing the RichText model files.}
    ';

    public $description = 'Installs the package.';

    private $afterMessages = [];

    public function handle()
    {
        $this->displayHeader('Installing Rich Text Laravel', '<bg=blue;fg=black> INFO </>');

        $this->displayTask('publishing config', fn () => $this->callSilently('vendor:publish', [
            '--tag' => 'rich-text-laravel-config',
            '--provider' => RichTextLaravelServiceProvider::class,
        ]));

        if (! $this->option('no-model')) {
            $this->displayTask('publishing migrations', fn () => $this->callSilently('vendor:publish', [
                '--tag' => 'rich-text-laravel-migrations',
                '--provider' => RichTextLaravelServiceProvider::class,
            ]));
        }

        $this->updateJsDependencies();
        $this->ensureTrixLibIsImported();
        $this->ensureTrixOverridesStylesIsPublished();
        $this->ensureTrixFieldComponentIsCopied();
        $this->updateAppLayoutFiles();

        $this->displayAfterNotes();

        $this->newLine();
        $this->line('<fg=white> Done!</>');
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
            'trix' => '^1.3.1',
        ];
    }

    private function updateJsDependenciesWithNpm(): void
    {
        $this->displayTask('adding JS dependencies (NPM)', function () {
            $this->updateNodePackages(function ($packages) {
                return $this->jsDependencies() + $packages;
            });

            $this->afterMessages[] = '<fg=white>* Run <fg=yellow>`npm install && npm run dev`</></>';

            return self::SUCCESS;
        });
    }

    private function installJsDependenciesWithImportmaps(): void
    {
        $this->displayTask('installing JS dependencies (Importmaps)', function () {
            $dependencies = array_keys($this->jsDependencies());

            return Artisan::call('importmap:pin ' . implode(' ', $dependencies));
        });
    }

    private function ensureTrixLibIsImported(): void
    {
        $trixRelativeDestinationPath = 'resources/js/libs/trix.js';

        $this->displayTask('publishing libs/trix.js file', function () use ($trixRelativeDestinationPath) {
            $trixAbsoluteDestinationPath = base_path($trixRelativeDestinationPath);

            if (File::exists($trixAbsoluteDestinationPath)) {
                $this->warn("File {$trixRelativeDestinationPath} already exists.");
                $this->afterMessages[] = '<fg=white>* The file `resources/js/libs/trix.js` already existed;</>';

                return self::INVALID;
            } else {
                File::ensureDirectoryExists(dirname($trixAbsoluteDestinationPath), recursive: true);
                File::copy(__DIR__ . '/../../resources/js/trix.js', $trixAbsoluteDestinationPath);

                return self::SUCCESS;
            }
        });

        $this->displayTask('importing the `libs/trix.js` file', function () {
            if (! File::exists(resource_path('js/app.js'))) {
                $this->afterMessages[] = sprintf('<fg=white>* Add `%s` to your main JS file.</>', sprintf("\nimport '%slibs/trix';\n", $this->usingImportmaps() ? '' : './'));

                return self::INVALID;
            }

            // If the import line doesn't exist on the js/app.js file, add it after the import
            // of the bootstrap.js file that ships with Laravel's default scaffolding.

            if (! preg_match(self::JS_TRIX_LIBS_IMPORT_PATTERN, File::get(resource_path('js/app.js')))) {
                File::put(resource_path('js/app.js'), preg_replace(
                    self::JS_BOOTSTRAP_IMPORT_PATTERN,
                    str_replace(
                        '%path%',
                        $this->usingImportmaps() ? '' : './',
                        <<<JS
                        \\1
                        import '%path%libs/trix';
                        JS,
                    ),
                    File::get(resource_path('js/app.js')),
                ));
            }

            return self::SUCCESS;
        });
    }

    private function ensureTrixOverridesStylesIsPublished(): void
    {
        $this->displayTask('publishing Trix styles', function () {
            File::copy(__DIR__ . '/../../resources/css/trix.css', resource_path('css/_trix.css'));

            return self::SUCCESS;
        });

        $this->displayTask('importing the css/_trix.css file', function () {
            if (File::exists($mainCssFile = resource_path('css/app.css')) && ! str_contains(File::get($mainCssFile), '_trix.css')) {
                File::prepend($mainCssFile, "@import './_trix.css';\n");

                return self::SUCCESS;
            } else {
                $this->afterMessages[] = '<fg=white>* Import the `resources/css/_trix.css` in your main CSS file;</>';

                return self::INVALID;
            }
        });
    }

    private function ensureTrixFieldComponentIsCopied(): void
    {
        $this->displayTask('publishing the <x-trix-field /> component', function () {
            File::ensureDirectoryExists(resource_path('views/components'));

            File::copy(
                __DIR__ . '/../../resources/views/components/trix-field.blade.php',
                resource_path('views/components/trix-field.blade.php'),
            );

            return self::SUCCESS;
        });
    }

    private function updateAppLayoutFiles(): void
    {
        $this->displayTask('updating layout files', function () {
            $this->existingLayoutFiles()
                ->each(fn ($file) => File::put(
                    $file,
                    preg_replace(
                        '/(\s*)(<\/head>)/',
                        "\\1    <x-rich-text-trix-styles />\n\\1\\2",
                        File::get($file),
                    ),
                ));

            return self::SUCCESS;
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
     * @param callable $callback
     * @param bool $dev
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
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
        );
    }

    private function displayTask($description, $task)
    {
        $width = (new Terminal())->getWidth();
        $dots = max(str_repeat('<fg=gray>.</>', $width - strlen($description) - 13), 0);
        $this->output->write(sprintf('    <fg=white>%s</> %s ', $description, $dots));
        $output = $task();

        if ($output === self::SUCCESS) {
            $this->output->write('<info>DONE</info>');
        } elseif ($output === self::FAILURE) {
            $this->output->write('<error>FAIL</error>');
        } elseif ($output === self::INVALID) {
            $this->output->write('<fg=yellow>WARN</>');
        }

        $this->newLine();
    }

    private function displayHeader($text, $prefix)
    {
        $this->newLine();
        $this->line(sprintf(' %s <fg=white>%s</>  ', $prefix, $text));
        $this->newLine();
    }

    private function displayAfterNotes()
    {
        if (count($this->afterMessages) > 0) {
            $this->displayHeader('After Notes & Next Steps', '<bg=yellow;fg=black> NOTES </>');

            foreach ($this->afterMessages as $message) {
                $this->line('    '.$message);
            }
        }
    }
}
