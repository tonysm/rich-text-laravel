<?php

declare(strict_types=1);

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tonysm\RichTextLaravel\Attributes\RichTextAttributes;
use Tonysm\RichTextLaravel\Content;
use Tonysm\RichTextLaravel\Exceptions\RichTextException;
use Tonysm\RichTextLaravel\Models\RichText;
use Tonysm\RichTextLaravel\Models\Traits\HasRichText;
use Tonysm\RichTextLaravel\RichTextLaravel;

class RichTextAttributesClassAttributeTest extends TestCase
{
    #[Test]
    public function stores_content_in_rich_texts_table_via_class_attribute(): void
    {
        $post = PostWithClassAttribute::create([
            'title' => 'My Post',
            'body' => '<h1>Hello World</h1>',
        ]);

        $this->assertEquals(1, DB::table('rich_texts')->where('record_id', $post->id)->count());
        $this->assertInstanceOf(RichText::class, $post->body);
    }

    #[Test]
    public function stores_attribute_based_content_via_class_attribute(): void
    {
        $page = PageWithClassAttribute::create([
            'title' => 'My Page',
            'body' => '<h1>Hello World</h1>',
        ]);

        $this->assertNotNull(DB::table('pages')->where('id', $page->id)->value('body'));
        $this->assertEquals(0, DB::table('rich_texts')->count());

        $page = $page->fresh();

        $this->assertInstanceOf(Content::class, $page->body);
    }

    #[Test]
    public function stores_encrypted_content_via_class_attribute(): void
    {
        $page = PageWithEncryptedClassAttribute::create([
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

    #[Test]
    public function mixed_attribute_and_relationship_fields_via_class_attribute(): void
    {
        $page = PageWithMixedClassAttributes::create([
            'title' => 'My Page',
            'body' => '<h1>Body content</h1>',
            'notes' => '<p>Notes content</p>',
        ]);

        $page = $page->fresh();

        $this->assertInstanceOf(Content::class, $page->body);
        $this->assertNotNull(DB::table('pages')->where('id', $page->id)->value('body'));

        $this->assertInstanceOf(RichText::class, $page->notes);
        $this->assertEquals(1, DB::table('rich_texts')->where('record_id', $page->id)->count());
    }

    #[Test]
    public function eager_loading_works_with_class_attribute(): void
    {
        PageWithMixedClassAttributes::create([
            'title' => 'Page 1',
            'body' => '<h1>Body 1</h1>',
            'notes' => '<p>Notes 1</p>',
        ]);

        PageWithMixedClassAttributes::create([
            'title' => 'Page 2',
            'body' => '<h1>Body 2</h1>',
            'notes' => '<p>Notes 2</p>',
        ]);

        $queryCounts = 0;
        DB::listen(function () use (&$queryCounts): void {
            $queryCounts++;
        });

        $pages = PageWithMixedClassAttributes::withRichText()->get();
        $pages->each(fn ($page) => $page->body && $page->notes);

        $this->assertEquals(2, $queryCounts);
    }

    #[Test]
    public function throws_when_both_class_attribute_and_property_are_used(): void
    {
        $this->expectException(RichTextException::class);
        $this->expectExceptionMessage('uses both');

        (new PostWithBothConfigStyles)->getRichTextFieldsPublic();
    }

    #[Test]
    public function throws_when_neither_class_attribute_nor_property_is_defined(): void
    {
        $this->expectException(RichTextException::class);
        $this->expectExceptionMessage('must declare rich text fields');

        (new PostWithNoConfig)->getRichTextFieldsPublic();
    }
}

#[RichTextAttributes(['body'])]
class PostWithClassAttribute extends Model
{
    use HasRichText;

    protected $table = 'posts';

    protected $guarded = [];
}

#[RichTextAttributes(['body' => ['attribute' => true]])]
class PageWithClassAttribute extends Model
{
    use HasRichText;

    protected $table = 'pages';

    protected $guarded = [];
}

#[RichTextAttributes(['body' => ['attribute' => true, 'encrypted' => true]])]
class PageWithEncryptedClassAttribute extends Model
{
    use HasRichText;

    protected $table = 'pages';

    protected $guarded = [];
}

#[RichTextAttributes(['body' => ['attribute' => true], 'notes'])]
class PageWithMixedClassAttributes extends Model
{
    use HasRichText;

    protected $table = 'pages';

    protected $guarded = [];
}

#[RichTextAttributes(['body'])]
class PostWithBothConfigStyles extends Model
{
    use HasRichText;

    protected $table = 'posts';

    protected $guarded = [];

    protected $richTextAttributes = ['body'];

    public function getRichTextFieldsPublic(): array
    {
        return $this->getRichTextFields();
    }
}

class PostWithNoConfig extends Model
{
    use HasRichText;

    protected $table = 'posts';

    protected $guarded = [];

    public function getRichTextFieldsPublic(): array
    {
        return $this->getRichTextFields();
    }
}
