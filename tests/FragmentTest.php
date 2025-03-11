<?php

namespace Tonysm\RichTextLaravel\Tests;

use DOMNode;
use Tonysm\RichTextLaravel\Fragment;
use Tonysm\RichTextLaravel\HtmlConversion;

class FragmentTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function creates_from_fragment(): void
    {
        $existing = new Fragment(
            HtmlConversion::fragmentForHtml('<h1>Hey there</h1>')->source
        );

        $fragment = Fragment::wrap($existing);

        $this->assertSame($existing, $fragment);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function creates_from_dom_fragment(): void
    {
        $source = HtmlConversion::fragmentForHtml('<h1>Hey there</h1>')->source;

        $fragment = Fragment::wrap($source);

        $this->assertInstanceOf(Fragment::class, $fragment);
        $this->assertSame($source, $fragment->source);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function creates_from_html(): void
    {
        $source = '<h1>hey there</h1>';

        $fragment = Fragment::wrap($source);

        $this->assertInstanceOf(Fragment::class, $fragment);
        $this->assertStringContainsString($source, $fragment->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function finds_all(): void
    {
        $source = '<div>content</div><a href="http://example.com">link one</a><a href="http:://example.com">second link</a>';

        $fragment = Fragment::wrap($source);

        $links = $fragment->findAll('//a');

        $this->assertNotFalse($links);
        $this->assertCount(2, $links);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function replaces_fragments(): void
    {
        $source = '<div>something that wont change</div><h1>old title</h1>';

        $fragment = Fragment::wrap($source);

        $newFragment = $fragment->replace('//h1', fn(DOMNode $node): \Tonysm\RichTextLaravel\Fragment => HtmlConversion::fragmentForHtml('<h1>new title</h1>'));

        $this->assertNotSame($fragment, $newFragment);
        $this->assertStringNotContainsString('<h1>old title</h1>', $newFragment->toHtml());
        $this->assertStringContainsString('<div>something that wont change</div><h1>new title</h1>', $newFragment->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_gives_us_a_new_instance(): void
    {
        $source = '<div>something that wont change</div><h1>old title</h1>';

        $fragment = Fragment::wrap($source);

        $newFragment = $fragment->update();

        $this->assertNotSame($fragment, $newFragment);
        $this->assertEquals($source, $newFragment->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_allows_passing_closure_to_tweak_the_source(): void
    {
        $source = '<div>something that wont change</div><h1>old title</h1>';

        $fragment = Fragment::wrap($source);

        $newFragment = $fragment->update(function ($passedSource) use ($fragment, $source): \DOMDocument {
            $this->assertNotSame($fragment->source, $passedSource);
            $this->assertStringContainsString($source, $passedSource->saveHtml());

            return HtmlConversion::fragmentForHtml('<div>source was updated</div>')->source;
        });

        $this->assertNotSame($fragment, $newFragment);
        $this->assertNotEquals($source, $newFragment->toHtml());
        $this->assertEquals('<div>source was updated</div>', $newFragment->toHtml());
    }
}
