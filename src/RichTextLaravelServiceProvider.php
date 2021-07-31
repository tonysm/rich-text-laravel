<?php

namespace Tonysm\RichTextLaravel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tonysm\RichTextLaravel\Commands\RichTextLaravelCommand;

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
            ->hasMigration('create_rich-text-laravel_table')
            ->hasCommand(RichTextLaravelCommand::class);
    }
}
