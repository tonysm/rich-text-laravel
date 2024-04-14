<?php

namespace Workbench\App\Providers;

use DOMElement;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;
use Tonysm\RichTextLaravel\RichTextLaravel;
use Workbench\App\Livewire\Posts;
use Workbench\App\Models\Opengraph\OpengraphEmbed;

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
        $this->registerCustomRichTextAttachables();
    }

    public function registerComponents(): void
    {
        $this->callAfterResolving('livewire', function (LivewireManager $livewire, Application $app) {
            $livewire->component('posts.index', Posts::class);
        });
    }

    public function registerCustomRichTextAttachables(): void
    {
        RichTextLaravel::withCustomAttachables(function (DOMElement $node) {
            if ($attachable = OpengraphEmbed::fromNode($node)) {
                return $attachable;
            }
        });
    }
}
