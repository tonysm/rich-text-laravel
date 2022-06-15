<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;

class TrixCoreStylesComponentTest extends TestCase
{
    use InteractsWithViews;

    /** @test */
    public function renders_core_styles_component()
    {
        $this->blade('<x-rich-text-trix-styles />')
            ->assertSee('<style ', escape: false)
            ->assertSee('</style>', escape: false)
            ->assertSee('trix-editor {', escape: false);
    }

    /** @test */
    public function renders_core_styles_component_with_attributes()
    {
        $this->blade('<x-rich-text-trix-styles nonce="lorem" />')
            ->assertSee('<style nonce="lorem">', escape: false)
            ->assertSee('</style>', escape: false)
            ->assertSee('trix-editor {', escape: false);
    }
}
