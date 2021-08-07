<?php

namespace Tonysm\RichTextLaravel\Tests\Actions\Rendering;

use Tonysm\RichTextLaravel\Actions\RenderAttachments;
use Tonysm\RichTextLaravel\Tests\TestCase;

class ConvertToPlainTextTest extends TestCase
{
    /** @test */
    public function p_tags_are_separated_by_two_new_lines()
    {
        $this->assertConvertedTo(
            "Hello World!\n\nHow are you?",
            '<p>Hello World!</p><p>How are you?</p>',
        );
    }

    /** @test */
    public function blockquote_tags_are_separated_by_two_new_lines()
    {
        $this->assertConvertedTo(
            "“Hello world!”\n\n“How are you?”",
            "<blockquote>Hello world!</blockquote><blockquote>How are you?</blockquote>"
        );
    }

    /** @test */
    public function ol_tags_are_separated_by_two_new_lines()
    {
        $this->assertConvertedTo(
            "Hello world!\n\n1. list1\n\n1. list2\n\nHow are you?",
            "<p>Hello world!</p><ol><li>list1</li></ol><ol><li>list2</li></ol><p>How are you?</p>"
        );
    }

    /** @test */
    public function ul_tags_are_separated_by_two_new_lines()
    {
        $this->assertConvertedTo(
            "Hello world!\n\n• list1\n\n• list2\n\nHow are you?",
            "<p>Hello world!</p><ul><li>list1</li></ul><ul><li>list2</li></ul><p>How are you?</p>"
        );
    }

    /** @test */
    public function h1_tags_are_separated_by_two_new_lines()
    {
        $this->assertConvertedTo(
            "Hello world!\n\nHow are you?",
            "<h1>Hello world!</h1><div>How are you?</div>"
        );
    }

    /** @test */
    public function li_tags_are_separated_by_one_new_line()
    {
        $this->assertConvertedTo(
            "• one\n• two\n• three",
            "<ul><li>one</li><li>two</li><li>three</li></ul>"
        );
    }

    /** @test */
    public function li_tags_without_parent_list()
    {
        $this->assertConvertedTo(
            "• one\n• two\n• three",
            "<li>one</li><li>two</li><li>three</li>"
        );
    }

    /** @test */
    public function br_are_separated_by_one_new_line()
    {
        $this->assertConvertedTo(
            "Hello world!\none\ntwo\nthree",
            "<p>Hello world!<br>one<br>two<br>three</p>"
        );
    }

    /** @test */
    public function divs_are_separated_by_one_new_line()
    {
        $this->assertConvertedTo(
            "Hello world!\nHow are you?",
            "<div>Hello world!</div><div>How are you?</div>"
        );
    }

    /** @test */
    public function figcaptions_are_converted_to_plain_text()
    {
        $this->assertConvertedTo(
            "Hello world! [A condor in the mountain]",
            "Hello world! <figcaption>A condor in the mountain</figcaption>"
        );
    }

    /** @test */
    public function rich_text_attachments_are_converted_to_plain_text()
    {
        $this->assertConvertedTo(
            "Hello world! [Cat]",
            'Hello world! <rich-text-attachment url="http://example.com/cat.jpg" content-type="image" caption="Cat"></rich-text-attachment>',
        );
    }

    /** @test */
    public function preserves_non_linebreaks_white_spaces()
    {
        $this->assertConvertedTo(
            "Hello world!",
            "<div><strong>Hello </strong>world!</div>"
        );
    }

    /** @test */
    public function preserves_trailing_linebreaks()
    {
        $this->assertConvertedTo(
            "Hello\nHow are you?",
            "<strong>H<i><em>e</em></i>llo<br></strong>How are you?"
        );
    }

    /** @test */
    public function handles_deeply_nested()
    {
        $deeply = "<div>How are you?</div>";

        foreach (range(1, 100) as $i) {
            $deeply = "<div>{$deeply}</div>";
        }

        $this->assertConvertedTo(
            "Hello world!\nHow are you?",
            "<div>Hello world!</div>{$deeply}",
        );
    }

    private function assertConvertedTo($expected, $content): void
    {
        $actual = (new RenderAttachments(plainText: true))($content);

        $this->assertEquals($expected, $actual);
    }
}
