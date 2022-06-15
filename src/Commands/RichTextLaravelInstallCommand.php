<?php

namespace Tonysm\RichTextLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tonysm\RichTextLaravel\RichTextLaravelServiceProvider;

class RichTextLaravelInstallCommand extends Command
{
    const JS_BOOTSTRAP_IMPORT_PATTERN = '/import [\'\"](?:\.\/)?bootstrap[\'\"];?/';
    const JS_TRIX_LIBS_IMPORT_PATTERN = '/import [\'\"](?:\.\/)?libs\/trix[\'\"];?/';
    const CSS_TAILWIND_BASE_LINE_PATTERN = '/\@import \"tailwindcss\/base\";/';

    public $signature = 'richtext:install
        {--no-model : Skip publishing the RichText model files.}
    ';

    public $description = 'Installs the package.';

    public function handle()
    {
        $this->call('vendor:publish', ['--tag' => 'rich-text-laravel-config', '--provider' => RichTextLaravelServiceProvider::class]);

        if (! $this->option('no-model')) {
            $this->call('vendor:publish', ['--tag' => 'rich-text-laravel-migrations', '--provider' => RichTextLaravelServiceProvider::class]);
        }

        $this->updateJsDependencies();
        $this->ensureTrixLibIsImported();
        $this->ensureTrixOverridesStylesIsPublished();
        $this->ensureTrixFieldComponentIsCopied();

        if (! $this->usingImportmaps()) {
            $this->info("to finish the installation you may run:");
            $this->warn("\nnpm install && npm run dev\n");
            $this->info("After that you should be good to go.");
        } else {
            $this->info('Done!');
        }
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
        $this->comment('Updating JS dependencies on your package.json file...');

        $this->updateNodePackages(function ($packages) {
            return $this->jsDependencies() + $packages;
        });
    }

    private function installJsDependenciesWithImportmaps(): void
    {
        $this->comment('Installing JS dependencies with Importmaps...');

        $dependencies = array_keys($this->jsDependencies());

        Artisan::call('importmap:pin ' . implode(' ', $dependencies));
    }

    private function ensureTrixLibIsImported(): void
    {
        $trixRelativeDestinationPath = 'resources/js/libs/trix.js';
        $trixAbsoluteDestinationPath = base_path($trixRelativeDestinationPath);

        if (File::exists($trixAbsoluteDestinationPath)) {
            $this->warn("File {$trixRelativeDestinationPath} already exists.");
        } else {
            File::ensureDirectoryExists(dirname($trixAbsoluteDestinationPath), recursive: true);
            File::copy(sprintf(__DIR__ . '/../../resources/js/trix-%s.js', $this->usingImportmaps() ? 'importmap' : 'webpack'), $trixAbsoluteDestinationPath);
            $this->info("The Trix setup JS file to your resources folder at: {$trixRelativeDestinationPath}.");
        }

        if (! File::exists(resource_path('js/app.js'))) {
            $this->info("Make sure to add the following line to your main JS file:");
            $this->warn(sprintf("\nimport '%slibs/trix';\n", $this->usingImportmaps() ? '' : './'));

            return;
        }

        // If the import line doesn't exist on the js/app.js file, add it after the import
        // of the bootstrap.js file that ships with Laravel's default scaffolding.

        if (! preg_match(self::JS_TRIX_LIBS_IMPORT_PATTERN, File::get(resource_path('js/app.js')))) {
            $this->comment('Adding the trix lib import line to your resources/js/app.js file...');

            File::put(resource_path('js/app.js'), preg_replace(
                self::JS_BOOTSTRAP_IMPORT_PATTERN,
                str_replace(
                    '%path%',
                    $this->usingImportmaps() ? '' : './',
                    <<<JS
                    import '%path%bootstrap';
                    import '%path%libs/trix';
                    JS,
                ),
                File::get(resource_path('js/app.js')),
            ));
        }
    }

    private function ensureTrixOverridesStylesIsPublished(): void
    {
        File::copy(__DIR__ . '/../../resources/css/trix.css', resource_path('css/_trix.css'));

        if (File::exists(resource_path('css/app.css')) && preg_match(self::CSS_TAILWIND_BASE_LINE_PATTERN, File::get(resource_path('css/app.css')))) {
            File::put(
                resource_path('css/app.css'),
                preg_replace(
                    self::CSS_TAILWIND_BASE_LINE_PATTERN,
                    <<<CSS
                    @import "tailwindcss/base";
                    @import "./_trix.css";
                    CSS,
                    File::get(resource_path('css/app.css')),
                ),
            );
        } else {
            $this->warn('Please, make sure you import the newly published css/_trix.css file to your main CSS file.');
        }
    }

    private function ensureTrixFieldComponentIsCopied(): void
    {
        File::ensureDirectoryExists(resource_path('views/components'));

        File::copy(
            __DIR__ . '/../../resources/views/components/trix-field.blade.php',
            resource_path('views/components/trix-field.blade.php'),
        );
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
}
