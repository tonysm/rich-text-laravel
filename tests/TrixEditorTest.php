<?php

namespace Tonysm\RichTextLaravel\Tests;

use PHPUnit\Framework\Attributes\Test;
use Tonysm\RichTextLaravel\Editor\TrixEditor;
use Tonysm\RichTextLaravel\Fragment;

class TrixEditorTest extends TestCase
{
    #[Test]
    public function as_canonical_transforms_fragment_for_storage(): void
    {
        $expected = '<div>hello, world</div>';
        $fragment = Fragment::wrap($expected);
        $editor = new TrixEditor;

        $this->assertEquals($expected, $editor->asCanonical($fragment)->toHtml());
    }

    #[Test]
    public function as_editable_transforms_fragment_for_editing(): void
    {
        $expected = '<div>hello, world</div>';
        $fragment = Fragment::wrap($expected);
        $editor = new TrixEditor;

        $this->assertEquals($expected, $editor->asEditable($fragment)->toHtml());
    }
}
