<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use PHPUnit\Framework\Attributes\Test;

class InputComponentTest extends TestCase
{
    use InteractsWithViews;

    #[Test]
    public function renders_trix_editor_by_default(): void
    {
        $this->blade('<x-rich-text::input id="content" />')
            ->assertSee('<trix-editor', escape: false)
            ->assertSee('id="content"', escape: false);
    }

    #[Test]
    public function renders_lexxy_editor_when_configured(): void
    {
        config()->set('rich-text-laravel.editor', 'lexxy');

        $this->blade('<x-rich-text::input id="content" />')
            ->assertSee('<lexxy-editor', escape: false)
            ->assertSee('id="content"', escape: false);
    }

    #[Test]
    public function renders_trix_component_directly(): void
    {
        $this->blade('<x-rich-text::trix id="content" />')
            ->assertSee('<trix-editor', escape: false)
            ->assertSee('<trix-toolbar', escape: false);
    }

    #[Test]
    public function renders_lexxy_component_directly(): void
    {
        $this->blade('<x-rich-text::lexxy id="content" />')
            ->assertSee('<lexxy-editor', escape: false);
    }

    #[Test]
    public function renders_lexxy_slots_directly(): void
    {
        config()->set('rich-text-laravel.editor', 'lexxy');

        $this->blade(<<<BLADE
            <x-rich-text::input id="content" name="content">
                <lexxy-prompt trigger="@" src="/mentions" name="mention"></lexxy-prompt>
            </x-rich-text::input>
            BLADE)
            ->assertSee('<lexxy-editor', escape: false)
            ->assertSee('<lexxy-prompt', escape: false);
    }

    #[Test]
    public function forwards_name_attribute(): void
    {
        $this->blade('<x-rich-text::input id="content" name="body" />')
            ->assertSee('name="body"', escape: false);
    }

    #[Test]
    public function forwards_value_attribute(): void
    {
        $this->blade('<x-rich-text::input id="content" value="Hello World" />')
            ->assertSee('Hello World');
    }
}
