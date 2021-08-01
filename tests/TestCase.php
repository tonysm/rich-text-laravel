<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase as Orchestra;
use Tonysm\RichTextLaravel\RichTextLaravelServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Tonysm\\RichTextLaravel\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        View::addLocation(__DIR__ . '/Stubs/views');
    }

    protected function getPackageProviders($app)
    {
        return [
            RichTextLaravelServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $this->setupDatabase();
    }

    protected function setupDatabase()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->longText('body');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
}
