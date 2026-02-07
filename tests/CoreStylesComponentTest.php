<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use PHPUnit\Framework\Attributes\Test;

class CoreStylesComponentTest extends TestCase
{
    use InteractsWithViews;

    #[Test]
    public function renders_core_styles_component(): void
    {
        $this->blade('<x-rich-text::styles />')
            ->assertSee('<style ', escape: false)
            ->assertSee('</style>', escape: false)
            ->assertSee('trix-editor {', escape: false);
    }

    #[Test]
    public function renders_core_styles_component_with_attributes(): void
    {
        $this->blade('<x-rich-text::styles nonce="lorem" />')
            ->assertSee('<style nonce="lorem">', escape: false)
            ->assertSee('</style>', escape: false)
            ->assertSee('trix-editor {', escape: false);
    }
}
