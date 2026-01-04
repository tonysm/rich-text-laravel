<?php

namespace Tonysm\RichTextLaravel\Tests;

use PHPUnit\Framework\Attributes\Test;
use Tonysm\RichTextLaravel\Fragment;
use Tonysm\RichTextLaravel\RichTextLaravel;
use Tonysm\RichTextLaravel\Tests\Fixtures\TestEditor;

class EditorTest extends TestCase
{
    #[Test]
    public function as_canonical_transforms_fragment_for_storage(): void
    {
        $fragment = Fragment::wrap(<<<'HTML'
        <test-editor-attachment sgid="some-sgid" content-type="plain/text"></test-editor-attachment>
        HTML);

        $editor = new TestEditor;

        $this->assertEquals(trim(<<<'HTML'
        <rich-text-attachment sgid="some-sgid" content-type="plain/text"></rich-text-attachment>
        HTML), trim($editor->asCanonical($fragment)->toHtml()));
    }

    #[Test]
    public function as_editable_transforms_fragment_for_editing(): void
    {
        $fragment = Fragment::wrap(<<<'HTML'
        <rich-text-attachment sgid="some-sgid" content-type="plain/text"></rich-text-attachment>
        HTML);

        $editor = new TestEditor;

        $this->assertEquals(trim(<<<'HTML'
        <test-editor-attachment sgid="some-sgid" content-type="plain/text"></test-editor-attachment>
        HTML), trim($editor->asEditable($fragment)->toHtml()));
    }

    #[Test]
    public function resolve_editor_from_config(): void
    {
        $this->assertNotNull(RichTextLaravel::editor());
    }
}
