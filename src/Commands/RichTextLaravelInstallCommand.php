<?php

namespace Tonysm\RichTextLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tonysm\RichTextLaravel\RichTextLaravelServiceProvider;

class RichTextLaravelInstallCommand extends Command
{
    public $signature = 'richtext:install {--no-model : Skip publishing the RichText model files.}';

    public $description = 'Installs the package.';

    public function handle()
    {
        $this->call('vendor:publish', ['--tag' => 'rich-text-laravel-config', '--provider' => RichTextLaravelServiceProvider::class]);

        if (! $this->option('no-model')) {
            $this->call('vendor:publish', ['--tag' => 'rich-text-laravel-migrations', '--provider' => RichTextLaravelServiceProvider::class]);
        }

        $this->updateNodePackages(function ($packages) {
            return [
                'trix' => '^1.3.1',
            ] + $packages;
        });

        $trixRelativeDestinationPath = 'resources/js/libs/trix.js';
        $trixAbsoluteDestinationPath = base_path($trixRelativeDestinationPath);

        if (File::exists($trixAbsoluteDestinationPath)) {
            $this->warn("File {$trixRelativeDestinationPath} already exists.");
        } else {
            File::makeDirectory(dirname($trixAbsoluteDestinationPath), recursive: true);
            File::copy(__DIR__ . '/../../resources/js/trix.js', $trixAbsoluteDestinationPath);
            $this->info("The Trix setup JS file to your resources folder at: {$trixRelativeDestinationPath}.");
        }

        $this->info("Make sure to add the following line to your main JS file:");
        $this->warn("\t import './libs/trix';");

        $this->info("Next, add these CSS lines to your CSS compilation process:\n");
        $this->warn(File::get(__DIR__ . '/../../resources/css/trix.css'));

        $this->info("Then, to finish the installation you may run:");
        $this->warn("\n\tnpm install && npm run dev\n");
        $this->info("After that you should be good to go.");
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
