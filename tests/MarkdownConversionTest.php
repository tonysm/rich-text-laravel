<?php

declare(strict_types=1);

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Content;
use Tonysm\RichTextLaravel\Fragment;
use Tonysm\RichTextLaravel\Models\RichText;
use Workbench\Database\Factories\UserFactory;

class MarkdownConversionTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function plain_text_passes_through_unchanged(): void
    {
        $this->assertConvertedTo('hello world', 'hello world');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function strong_tags_are_converted_to_bold(): void
    {
        $this->assertConvertedTo('**hello**', '<strong>hello</strong>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function b_tags_are_converted_to_bold(): void
    {
        $this->assertConvertedTo('**hello**', '<b>hello</b>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function em_tags_are_converted_to_italic(): void
    {
        $this->assertConvertedTo('*hello*', '<em>hello</em>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function i_tags_are_converted_to_italic(): void
    {
        $this->assertConvertedTo('*hello*', '<i>hello</i>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function s_tags_are_converted_to_strikethrough(): void
    {
        $this->assertConvertedTo('~~hello~~', '<s>hello</s>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function code_tags_are_converted_to_inline_code(): void
    {
        $this->assertConvertedTo('`hello`', '<code>hello</code>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nested_strong_and_em_tags_produce_bold_italic(): void
    {
        $this->assertConvertedTo('***hello***', '<strong><em>hello</em></strong>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function redundant_nested_b_and_strong_tags_do_not_double_bold(): void
    {
        $this->assertConvertedTo('**hello**', '<b><strong>hello</strong></b>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function strong_with_inner_whitespace_moves_spaces_outside_markers(): void
    {
        $this->assertConvertedTo('a **hello** b', '<p>a<strong> hello </strong>b</p>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function em_with_inner_whitespace_moves_spaces_outside_markers(): void
    {
        $this->assertConvertedTo('a *hello* b', '<p>a<em> hello </em>b</p>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function b_wrapping_code_wrapping_strong_collapses_to_bold_code(): void
    {
        $this->assertConvertedTo('**`asdf`**', '<b><code><strong>asdf</strong></code></b>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function adjacent_b_spans_are_merged(): void
    {
        $this->assertConvertedTo(
            'aaa **`bb` or `cc`** ddd',
            '<p>aaa <b><code><strong>bb</strong></code></b><b><strong> or </strong></b><b><code><strong>cc</strong></code></b> ddd</p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function adjacent_i_spans_are_merged(): void
    {
        $this->assertConvertedTo(
            '*`foo` and `bar`*',
            '<p><i><code><em>foo</em></code></i><i><em> and </em></i><i><code><em>bar</em></code></i></p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function adjacent_bold_and_italic_spans_are_merged(): void
    {
        $this->assertConvertedTo(
            '***`foo`** **and** **`bar`***',
            '<p><i><b><code><strong>foo</strong></code></b></i><i><b><strong> and </strong></b></i><i><b><code><strong>bar</strong></code></b></i></p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function p_tags_are_separated_by_two_new_lines(): void
    {
        $this->assertConvertedTo(
            "hello\n\nworld",
            '<p>hello</p><p>world</p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function h1_through_h6_tags_are_converted_to_heading_markers(): void
    {
        $this->assertConvertedTo('# hello', '<h1>hello</h1>');
        $this->assertConvertedTo('## hello', '<h2>hello</h2>');
        $this->assertConvertedTo('### hello', '<h3>hello</h3>');
        $this->assertConvertedTo('#### hello', '<h4>hello</h4>');
        $this->assertConvertedTo('##### hello', '<h5>hello</h5>');
        $this->assertConvertedTo('###### hello', '<h6>hello</h6>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function blockquote_tags_are_converted_to_quoted_lines(): void
    {
        $this->assertConvertedTo('> hello', '<blockquote>hello</blockquote>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function blockquote_with_multiple_lines_prefixes_each_line(): void
    {
        $this->assertConvertedTo(
            "> line1\n> line2",
            "<blockquote>line1\nline2</blockquote>"
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nested_blockquote_tags_produce_nested_quotes(): void
    {
        $this->assertConvertedTo(
            "> this is a quote\n> > of a quote",
            '<blockquote>this is a quote<blockquote>of a quote</blockquote></blockquote>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function br_tags_are_converted_to_newlines(): void
    {
        $this->assertConvertedTo("hello\nworld", 'hello<br>world');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function hr_tags_are_converted_to_thematic_breaks(): void
    {
        $this->assertConvertedTo('---', '<hr>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function pre_tags_are_converted_to_fenced_code_blocks(): void
    {
        $this->assertConvertedTo("```\nhello\n```", '<pre>hello</pre>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function pre_with_nested_code_tag_is_converted_to_fenced_code_block(): void
    {
        $this->assertConvertedTo("```\nhello\n```", '<pre><code>hello</code></pre>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function pre_followed_by_p_has_blank_line_separator(): void
    {
        $this->assertConvertedTo(
            "```\nhello\n```\n\nworld",
            '<pre>hello</pre><p>world</p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ul_tags_are_converted_to_unordered_lists(): void
    {
        $this->assertConvertedTo(
            "before\n\n- one\n- two\n- three\n\nafter",
            '<p>before</p><ul><li>one</li><li>two</li><li>three</li></ul><p>after</p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ol_tags_are_converted_to_ordered_lists(): void
    {
        $this->assertConvertedTo(
            "before\n\n1. one\n2. two\n3. three\n\nafter",
            '<p>before</p><ol><li>one</li><li>two</li><li>three</li></ol><p>after</p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function empty_li_tags_are_skipped(): void
    {
        $this->assertConvertedTo(
            "before\n\n- real\n\nafter",
            '<p>before</p><ul><li></li><li>real</li></ul><p>after</p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nested_ul_tags_are_indented(): void
    {
        $this->assertConvertedTo(
            "- one\n  - nested\n- two",
            '<ul><li>one<ul><li>nested</li></ul></li><li>two</li></ul>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nested_ul_where_sublist_is_in_its_own_li(): void
    {
        $this->assertConvertedTo(
            "- top 1\n- top 2\n  - nested 1\n  - nested 2",
            <<<'HTML'
                <ul>
                  <li>top 1</li>
                  <li>top 2</li>
                  <li>
                    <ul>
                      <li>nested 1</li>
                      <li>nested 2</li>
                    </ul>
                  </li>
                </ul>
            HTML
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ul_followed_by_p_has_blank_line_separator(): void
    {
        $this->assertConvertedTo(
            "- Item 1\n  - Subitem\n- Item 2\n\nParagraph",
            <<<'HTML'
                <ul>
                  <li>Item 1</li>
                  <li>
                    <ul>
                      <li>Subitem</li>
                    </ul>
                  </li>
                  <li>Item 2</li>
                </ul>
                <p>Paragraph</p>
            HTML
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_tags_are_converted_to_links(): void
    {
        $this->assertConvertedTo(
            '[click here](https://example.com)',
            '<a href="https://example.com">click here</a>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_tags_with_formatting_inside(): void
    {
        $this->assertConvertedTo(
            '[**bold link**](https://example.com)',
            '<a href="https://example.com"><strong>bold link</strong></a>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_tags_without_href_pass_through_content(): void
    {
        $this->assertConvertedTo('**click here**', '<a><strong>click here</strong></a>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_tags_with_mailto_href_are_converted_to_links(): void
    {
        $this->assertConvertedTo('[email](mailto:test@example.com)', '<a href="mailto:test@example.com">email</a>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_tags_with_tel_href_are_converted_to_links(): void
    {
        $this->assertConvertedTo('[call](tel:+1234567890)', '<a href="tel:+1234567890">call</a>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_tags_with_relative_href_are_converted_to_links(): void
    {
        $this->assertConvertedTo('[page](/about)', '<a href="/about">page</a>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_tags_with_relative_href_containing_colons_are_converted_to_links(): void
    {
        $this->assertConvertedTo('[notes](/docs/v1:notes)', '<a href="/docs/v1:notes">notes</a>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_tags_with_javascript_href_pass_through_content_without_link(): void
    {
        $this->assertConvertedTo('click here', '<a href="javascript:alert(1)">click here</a>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function table_with_thead_is_converted_to_markdown_table(): void
    {
        $this->assertConvertedTo(
            "| Name | Age |\n| --- | --- |\n| Alice | 30 |",
            <<<'HTML'
                <table>
                  <thead><tr><th>Name</th><th>Age</th></tr></thead>
                  <tbody><tr><td>Alice</td><td>30</td></tr></tbody>
                </table>
            HTML
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function table_cells_with_formatting(): void
    {
        $this->assertConvertedTo(
            '| **bold** | *italic* |',
            '<table><tr><td><strong>bold</strong></td><td><em>italic</em></td></tr></table>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function table_without_thead(): void
    {
        $this->assertConvertedTo(
            "| a | b |\n| c | d |",
            '<table><tr><td>a</td><td>b</td></tr><tr><td>c</td><td>d</td></tr></table>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function table_with_th_headers_in_tbody(): void
    {
        $this->assertConvertedTo(
            "| a | b | c |\n| --- | --- | --- |\n| 1 | asdf | asdf |\n| 2 | asdf | asdf |",
            '<table><tbody><tr><th><p>a</p></th><th><p>b</p></th><th><p>c</p></th></tr><tr><th><p>1</p></th><td><p>asdf</p></td><td><p>asdf</p></td></tr><tr><th><p>2</p></th><td><p>asdf</p></td><td><p>asdf</p></td></tr></tbody></table>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function details_and_summary_are_converted(): void
    {
        $this->assertConvertedTo(
            "**Click to expand**\n\nHidden content",
            '<details><summary>Click to expand</summary>Hidden content</details>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function empty_content(): void
    {
        $this->assertConvertedTo('', '');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function leading_and_trailing_whitespace_is_stripped(): void
    {
        $this->assertConvertedTo('hello', '<p>  hello  </p>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function html_entities_are_decoded(): void
    {
        $this->assertConvertedTo(
            'asdf < asdf & asdf > asdf',
            '<p>asdf &lt; asdf &amp; asdf &gt; asdf</p>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unknown_elements_pass_through_their_content(): void
    {
        $this->assertConvertedTo('hello', '<asdf>hello</asdf>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function div_passes_through_content(): void
    {
        $this->assertConvertedTo('hello', '<div>  hello  </div>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function span_passes_through_content(): void
    {
        $this->assertConvertedTo('hello', '<span>  hello  </span>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function script_tags_are_ignored(): void
    {
        $this->assertConvertedTo(
            'hello',
            <<<'HTML'
                <script type="javascript">
                  console.log("message");
                </script>
                <div>hello</div>
            HTML
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function style_tags_are_ignored(): void
    {
        $this->assertConvertedTo(
            'hello',
            <<<'HTML'
                <style type="text/css">
                  body { color: red; }
                </style>
                <div>hello</div>
            HTML
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function image_attachment_with_caption_is_converted_to_markdown_image(): void
    {
        $this->assertConvertedTo(
            '![A photo](https://example.com/photo.png)',
            '<rich-text-attachment content-type="image/png" url="https://example.com/photo.png" caption="A photo"></rich-text-attachment>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function image_attachment_without_caption_falls_back_to_image_alt_text(): void
    {
        $this->assertConvertedTo(
            '![Image](https://example.com/photo.jpg)',
            '<rich-text-attachment content-type="image/jpeg" url="https://example.com/photo.jpg" filename="photo.jpg"></rich-text-attachment>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function content_attachment_html_is_converted_to_markdown(): void
    {
        $this->assertConvertedTo(
            '**hello**',
            '<rich-text-attachment content-type="text/html" content="<strong>hello</strong>"></rich-text-attachment>'
        );

        $this->assertConvertedTo(
            '**hello**',
            '<rich-text-attachment content-type="text/html" content="&lt;strong&gt;hello&lt;/strong&gt;"></rich-text-attachment>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function attachment_with_surrounding_text(): void
    {
        $this->assertConvertedTo(
            'Hello world! ![Cat](http://example.com/cat.jpg)',
            'Hello world! <rich-text-attachment url="http://example.com/cat.jpg" content-type="image/jpeg" caption="Cat"></rich-text-attachment>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_attachment_is_converted_to_markdown_with_caption(): void
    {
        $user = UserFactory::new()->create(['name' => 'Test User']);
        $sgid = $user->richTextSgid();

        $html = <<<HTML
        <rich-text-attachment sgid="{$sgid}" caption="Captioned"></rich-text-attachment>
        HTML;

        // User model doesn't implement richTextAsMarkdown, so it falls back to caption only
        $this->assertEquals('Captioned', (new Content($html))->toMarkdown());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_attachment_without_caption(): void
    {
        $user = UserFactory::new()->create(['name' => 'Test User']);
        $sgid = $user->richTextSgid();

        $html = <<<HTML
        <rich-text-attachment sgid="{$sgid}"></rich-text-attachment>
        HTML;

        // Users don't have richTextAsMarkdown, so it falls back to caption which is empty
        $this->assertEquals('', (new Content($html))->toMarkdown());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function missing_attachable_is_converted_to_box(): void
    {
        $user = UserFactory::new()->create(['name' => 'Test User']);
        $sgid = $user->richTextSgid();

        $html = <<<HTML
        <rich-text-attachment sgid="{$sgid}"></rich-text-attachment>
        HTML;

        $user->delete();

        $this->assertEquals('☒', (new Content($html))->toMarkdown());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rich_text_to_markdown(): void
    {
        $richText = new RichText(['body' => '<p><strong>hello</strong></p>']);
        $this->assertEquals('**hello**', $richText->toMarkdown());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rich_text_to_markdown_handles_blank_body(): void
    {
        $richText = new RichText(['body' => '']);
        $this->assertEquals('', $richText->toMarkdown());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rich_text_to_markdown_handles_nil_body(): void
    {
        $richText = new RichText(['body' => null]);
        $this->assertEquals('', $richText->toMarkdown());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function multiple_attachments_separated_by_whitespace_preserve_the_whitespace(): void
    {
        $this->assertConvertedTo(
            '![A](https://example.com/a.jpg) ![B](https://example.com/b.jpg)',
            '<rich-text-attachment content-type="image/jpeg" url="https://example.com/a.jpg" caption="A"></rich-text-attachment> <rich-text-attachment content-type="image/jpeg" url="https://example.com/b.jpg" caption="B"></rich-text-attachment>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function fragment_to_markdown_memoizes_the_result(): void
    {
        $fragment = Fragment::fromHtml('<p><strong>hello</strong></p>');
        $this->assertSame($fragment->toMarkdown(), $fragment->toMarkdown());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function whitespace_between_adjacent_code_elements_is_preserved(): void
    {
        $this->assertConvertedTo('`a` `b`', '<code>a</code> <code>b</code>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function whitespace_between_adjacent_strikethrough_elements_is_preserved(): void
    {
        $this->assertConvertedTo('~~a~~ ~~b~~', '<s>a</s> <s>b</s>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function a_tag_with_brackets_in_text_and_parens_in_url_is_escaped(): void
    {
        $this->assertConvertedTo(
            '[click \\[here\\]](https://example.com/page_\\(1\\))',
            '<a href="https://example.com/page_(1)">click [here]</a>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function image_attachment_with_parens_in_url_is_escaped(): void
    {
        $this->assertConvertedTo(
            '![Photo](https://example.com/image_\\(1\\).jpg)',
            '<rich-text-attachment content-type="image/jpeg" url="https://example.com/image_(1).jpg" caption="Photo"></rich-text-attachment>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function file_attachment_is_converted_to_markdown_link(): void
    {
        $this->assertConvertedTo(
            '[report.pdf](https://example.com/report.pdf)',
            '<rich-text-attachment content-type="application/pdf" url="https://example.com/report.pdf" filename="report.pdf"></rich-text-attachment>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function file_attachment_with_parens_in_url_is_escaped(): void
    {
        $this->assertConvertedTo(
            '[report.pdf](https://example.com/report_\\(1\\).pdf)',
            '<rich-text-attachment content-type="application/pdf" url="https://example.com/report_(1).pdf" filename="report.pdf"></rich-text-attachment>'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nested_s_tags_do_not_double_wrap_strikethrough(): void
    {
        $this->assertConvertedTo('~~hello~~', '<s><s>hello</s></s>');
    }

    private function assertConvertedTo(string $expected, string $html): void
    {
        $actual = (new Content($html))->toMarkdown();
        $this->assertEquals($expected, $actual);
    }
}
