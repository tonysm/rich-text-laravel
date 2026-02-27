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
            ->assertSee('<link', escape: false)
            ->assertSee('trix')
            ->assertSee('trix-rich-text-laravel-attachments');
    }

    #[Test]
    public function renders_core_styles_component_with_attributes(): void
    {
        $this->blade('<x-rich-text::styles nonce="lorem" />')
            ->assertSee('<link nonce="lorem"', escape: false)
            ->assertSee('trix')
            ->assertSee('trix-rich-text-laravel-attachments');
    }

    #[Test]
    public function renders_core_styles_component_with_theme(): void
    {
        $this->blade('<x-rich-text::styles theme="richtextlaravel" />')
            ->assertSee('<link', escape: false)
            ->assertSee('trix-rich-text-laravel')
            ->assertSee('trix-rich-text-laravel-attachments');
    }

    #[Test]
    public function renders_core_styles_component_with_attributes_and_theme(): void
    {
        $this->blade('<x-rich-text::styles nonce="lorem" theme="richtextlaravel" />')
            ->assertSee('<link nonce="lorem"', escape: false)
            ->assertSee('trix-rich-text-laravel')
            ->assertSee('trix-rich-text-laravel-attachments');
    }

    #[Test]
    public function renders_lexxy_styles(): void
    {
        config()->set('rich-text-laravel.editor', 'lexxy');

        $this->blade('<x-rich-text::styles />')
            ->assertSee('<link ', escape: false)
            ->assertSee('lexxy')
            ->assertSee('lexxy-rich-text-laravel-attachments');
    }

    #[Test]
    public function renders_lexxy_styles_with_attributes(): void
    {
        config()->set('rich-text-laravel.editor', 'lexxy');

        $this->blade('<x-rich-text::styles nonce="lorem" />')
            ->assertSee('<link nonce="lorem"', escape: false)
            ->assertSee('lexxy')
            ->assertSee('lexxy-rich-text-laravel-attachments');
    }
}
