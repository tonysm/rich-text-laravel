<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Content;

class PlainTextConversionTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function p_tags_are_separated_by_two_new_lines(): void
    {
        $this->assertConvertedTo(
            "Hello World!\n\nHow are you?",
            '<p>Hello World!</p><p>How are you?</p>',
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function blockquote_tags_are_separated_by_two_new_lines(): void
    {
        $this->assertConvertedTo(
            "“Hello world!”\n\n“How are you?”",
            '<blockquote>Hello world!</blockquote><blockquote>How are you?</blockquote>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ol_tags_are_separated_by_two_new_lines(): void
    {
        $this->assertConvertedTo(
            "Hello world!\n\n1. list1\n\n1. list2\n\nHow are you?",
            '<p>Hello world!</p><ol><li>list1</li></ol><ol><li>list2</li></ol><p>How are you?</p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ul_tags_are_separated_by_two_new_lines(): void
    {
        $this->assertConvertedTo(
            "Hello world!\n\n• list1\n\n• list2\n\nHow are you?",
            '<p>Hello world!</p><ul><li>list1</li></ul><ul><li>list2</li></ul><p>How are you?</p>'
        );
    }

    public static function headings(): array
    {
        return [
            'h1' => [
                'expected' => "Hello world!\n\nHow are you?",
                'content' => '<h1>Hello world!</h1><div>How are you?</div>',
            ],
            'h2' => [
                'expected' => "Hello world!\n\nHow are you?",
                'content' => '<h2>Hello world!</h2><div>How are you?</div>',
            ],
            'h3' => [
                'expected' => "Hello world!\n\nHow are you?",
                'content' => '<h3>Hello world!</h3><div>How are you?</div>',
            ],
            'h4' => [
                'expected' => "Hello world!\n\nHow are you?",
                'content' => '<h4>Hello world!</h4><div>How are you?</div>',
            ],
            'h5' => [
                'expected' => "Hello world!\n\nHow are you?",
                'content' => '<h5>Hello world!</h5><div>How are you?</div>',
            ],
            'h6' => [
                'expected' => "Hello world!\n\nHow are you?",
                'content' => '<h6>Hello world!</h6><div>How are you?</div>',
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('headings')]
    public function heading_tags_are_separated_by_two_new_lines(string $expected, string $content): void
    {
        $this->assertConvertedTo($expected, $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function li_tags_are_separated_by_one_new_line(): void
    {
        $this->assertConvertedTo(
            "• one\n• two\n• three",
            '<ul><li>one</li><li>two</li><li>three</li></ul>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function li_tags_without_parent_list(): void
    {
        $this->assertConvertedTo(
            "• one\n• two\n• three",
            '<li>one</li><li>two</li><li>three</li>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function basic_nested_ul_tags_are_indented(): void
    {
        $this->assertConvertedTo(
            "• Item 1\n  • Item 2",
            '<ul><li>Item 1<ul><li>Item 2</li></ul></li></ul>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function basic_nested_ol_tags_are_indented(): void
    {
        $this->assertConvertedTo(
            "1. Item 1\n  1. Item 2",
            '<ol><li>Item 1<ol><li>Item 2</li></ol></li></ol>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function complex_nested_and_mixed_list_tags_are_indented(): void
    {
        $this->assertConvertedTo(
            "• Item 0\n• Item 1\n  • Item A\n    1. Item i\n    2. Item ii\n  • Item B\n    • Item i\n• Item 2",
            '<ul><li>Item 0</li><li>Item 1<ul><li>Item A<ol><li>Item i</li><li>Item ii</li></ol></li><li>Item B<ul><li>Item i</li></ul></li></ul></li><li>Item 2</li></ul>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function br_are_separated_by_one_new_line(): void
    {
        $this->assertConvertedTo(
            "Hello world!\none\ntwo\nthree",
            '<p>Hello world!<br>one<br>two<br>three</p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function divs_are_separated_by_one_new_line(): void
    {
        $this->assertConvertedTo(
            "Hello world!\n\nHow are you?",
            '<div>Hello world!</div><div>How are you?</div>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function figcaptions_are_converted_to_plain_text(): void
    {
        $this->assertConvertedTo(
            'Hello world! [A condor in the mountain]',
            'Hello world! <figcaption>A condor in the mountain</figcaption>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rich_text_attachments_are_converted_to_plain_text(): void
    {
        $this->assertConvertedTo(
            'Hello world! [Cat]',
            'Hello world! <rich-text-attachment url="http://example.com/cat.jpg" content-type="image" caption="Cat"></rich-text-attachment>',
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function preserves_non_linebreaks_white_spaces(): void
    {
        $this->assertConvertedTo(
            'Hello world!',
            '<div><strong>Hello </strong>world!</div>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function preserves_trailing_linebreaks(): void
    {
        $this->assertConvertedTo(
            "Hello\nHow are you?",
            '<strong>H<i><em>e</em></i>llo<br></strong>How are you?'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function handles_deeply_nested(): void
    {
        // @TODO: refactor this for big documents. We should use while loops instead of recursively looping through the document.
        ini_set('xdebug.max_nesting_level', 1000);

        $deeply = '<div>How are you?</div>';

        foreach (range(1, 100) as $i) {
            $deeply = "<div>{$deeply}</div>";
        }

        $this->assertConvertedTo(
            "Hello world!\n\nHow are you?",
            "<div>Hello world!</div>{$deeply}",
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function converts_html_content(): void
    {
        $this->assertConvertedTo(
            "Hello\n\n\n\nWorld",
            trim(<<<'HTML'
            <div>Hello</div>
            <br>
            <figure data-trix-attachment='{"contentType": "text/html", "content": "<hr>"}'></figure>
            <br>
            <div>World</div>
            HTML)
        );

        $this->assertConvertedTo(
            "Hello\n\n\nhello\nWorld",
            trim(<<<'HTML'
            <div>Hello</div>
            <br>
            <figure data-trix-attachment='{"contentType": "text/html", "content": "<p>hello</p>"}'></figure>
            <br>
            <div>World</div>
            HTML)
        );
    }

    private function assertConvertedTo(string $expected, string $content): void
    {
        $actual = (new Content($content))->toPlainText();

        $this->assertEquals($expected, $actual);
    }
}
