<?php

namespace Tonysm\RichTextLaravel;

use Illuminate\View\Compilers\BladeCompiler;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tonysm\RichTextLaravel\Commands\InstallCommand;

class RichTextLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('rich-text-laravel')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_rich_texts_table')
            ->hasCommand(InstallCommand::class);
    }

    public function packageBooted()
    {
        $this->callAfterResolving('blade.compiler', function (BladeCompiler $blade) {
            $blade->anonymousComponentPath(dirname(__DIR__).implode(DIRECTORY_SEPARATOR, ['', 'resources', 'views', 'components']), 'rich-text');
        });
    }
}
