<?php

namespace Workbench\App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;
use Workbench\App\Livewire\Posts;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerComponents();
    }

    public function registerComponents(): void
    {
        $this->callAfterResolving('livewire', function (LivewireManager $livewire, Application $app) {
            $livewire->component('posts.index', Posts::class);
        });
    }
}
