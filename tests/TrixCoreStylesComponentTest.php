<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;

class TrixCoreStylesComponentTest extends TestCase
{
    use InteractsWithViews;

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_core_styles_component(): void
    {
        $this->blade('<x-rich-text::styles />')
            ->assertSee('<style ', escape: false)
            ->assertSee('</style>', escape: false)
            ->assertSee('trix-editor {', escape: false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_core_styles_component_with_attributes(): void
    {
        $this->blade('<x-rich-text::styles nonce="lorem" />')
            ->assertSee('<style nonce="lorem">', escape: false)
            ->assertSee('</style>', escape: false)
            ->assertSee('trix-editor {', escape: false);
    }
}
