<?php

declare(strict_types=1);

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Support\Facades\DB;
use Tonysm\RichTextLaravel\Content;
use Tonysm\RichTextLaravel\RichTextLaravel;
use Workbench\App\Models\Page;

class AttributeRichTextTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function stores_content_in_model_attribute(): void
    {
        $page = Page::create([
            'title' => 'My Page',
            'body' => '<h1>Hello World</h1>',
        ]);

        $this->assertNotNull(DB::table('pages')->where('id', $page->id)->value('body'));
        $this->assertEquals(0, DB::table('rich_texts')->count(), 'Content should not be stored in the rich_texts table.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function returns_content_instance(): void
    {
        $page = Page::create([
            'title' => 'My Page',
            'body' => '<h1>Hello World</h1>',
        ]);

        $page = $page->fresh();

        $this->assertInstanceOf(Content::class, $page->body);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_content(): void
    {
        $page = Page::create([
            'title' => 'My Page',
            'body' => '<h1>Hello World</h1>',
        ]);

        $page = $page->fresh();

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
            <h1>Hello World</h1>
        </div>

        HTML, "{$page->body}");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function converts_to_plain_text(): void
    {
        $page = Page::create([
            'title' => 'My Page',
            'body' => '<h1>Hello World</h1>',
        ]);

        $page = $page->fresh();

        $this->assertEquals('Hello World', $page->body->toPlainText());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function handles_empty_content(): void
    {
        $page = Page::create([
            'title' => 'My Page',
            'body' => '',
        ]);

        $page = $page->fresh();

        $this->assertInstanceOf(Content::class, $page->body);
        $this->assertTrue($page->body->isEmpty());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function handles_null_content(): void
    {
        $page = Page::create([
            'title' => 'My Page',
            'body' => null,
        ]);

        $page = $page->fresh();

        $this->assertInstanceOf(Content::class, $page->body);
        $this->assertTrue($page->body->isEmpty());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updates_content(): void
    {
        $page = Page::create([
            'title' => 'My Page',
            'body' => '<h1>Old Value</h1>',
        ]);

        $page->refresh()->update([
            'body' => '<h1>New Value</h1>',
        ]);

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
            <h1>New Value</h1>
        </div>

        HTML, "{$page->refresh()->body}");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function does_not_register_relationship_for_attribute_fields(): void
    {
        $page = new Page;

        $this->assertFalse($page->isRelation('richTextBody'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function with_rich_text_scope_skips_attribute_fields(): void
    {
        Page::create([
            'title' => 'My Page',
            'body' => '<h1>Hello</h1>',
        ]);

        // Should not throw, even though 'body' is attribute-based
        // and has no relationship to eager load.
        $pages = Page::withRichText()->get();

        $this->assertCount(1, $pages);
        $this->assertInstanceOf(Content::class, $pages->first()->body);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function mixed_attribute_and_relationship_fields(): void
    {
        $page = PageWithMixedFields::create([
            'title' => 'My Page',
            'body' => '<h1>Body content</h1>',
            'notes' => '<p>Notes content</p>',
        ]);

        $page = $page->fresh();

        // body is stored as attribute (in pages.body column)
        $this->assertInstanceOf(Content::class, $page->body);
        $this->assertNotNull(DB::table('pages')->where('id', $page->id)->value('body'));

        // notes is stored as relationship (in rich_texts table)
        $this->assertInstanceOf(\Tonysm\RichTextLaravel\Models\RichText::class, $page->notes);
        $this->assertEquals(1, DB::table('rich_texts')->where('record_id', $page->id)->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function eager_loading_with_mixed_fields_only_loads_relationship_fields(): void
    {
        PageWithMixedFields::create([
            'title' => 'Page 1',
            'body' => '<h1>Body 1</h1>',
            'notes' => '<p>Notes 1</p>',
        ]);

        PageWithMixedFields::create([
            'title' => 'Page 2',
            'body' => '<h1>Body 2</h1>',
            'notes' => '<p>Notes 2</p>',
        ]);

        $queryCounts = 0;
        DB::listen(function () use (&$queryCounts): void {
            $queryCounts++;
        });

        // withRichText() should only eager load 'notes' (relationship-based),
        // not 'body' (attribute-based). So: 1 query for pages + 1 for notes.
        $pages = PageWithMixedFields::withRichText()->get();
        $pages->each(fn ($page) => $page->body && $page->notes);

        $this->assertEquals(2, $queryCounts);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function encrypted_attribute_stores_encrypted_content(): void
    {
        $page = PageWithEncryptedAttribute::create([
            'title' => 'Secret Page',
            'body' => '<h1>Secret Content</h1>',
        ]);

        $rawBody = DB::table('pages')->where('id', $page->id)->value('body');

        $this->assertStringNotContainsString('Secret Content', $rawBody);
        $this->assertStringContainsString('Secret Content', RichTextLaravel::decrypt($rawBody, $page, 'body'));

        $page = $page->fresh();

        $this->assertInstanceOf(Content::class, $page->body);
        $this->assertStringContainsString('Secret Content', $page->body->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function canonicalizes_content_on_storage(): void
    {
        $trixHtml = <<<'HTML'
        <h1>Hello</h1>
        <figure data-trix-attachment='{"contentType":"image\/png","url":"http:\/\/example.com\/image.png","filename":"image.png","filesize":100,"width":200,"height":100}'></figure>
        HTML;

        $page = Page::create([
            'title' => 'My Page',
            'body' => $trixHtml,
        ]);

        $rawBody = DB::table('pages')->where('id', $page->id)->value('body');

        // The content should be canonicalized (Trix figure converted to rich-text-attachment)
        $this->assertStringContainsString('rich-text-attachment', $rawBody);
    }
}

class PageWithMixedFields extends Page
{
    protected $table = 'pages';

    protected $richTextAttributes = [
        'body' => ['attribute' => true],
        'notes',
    ];
}

class PageWithEncryptedAttribute extends Page
{
    protected $table = 'pages';

    protected $richTextAttributes = [
        'body' => ['attribute' => true, 'encrypted' => true],
    ];
}
