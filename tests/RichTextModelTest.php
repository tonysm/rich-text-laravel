<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tonysm\RichTextLaravel\Content;
use Tonysm\RichTextLaravel\Models\RichText;
use Tonysm\RichTextLaravel\Tests\Stubs\HasRichText\Post;

class RichTextModelTest extends TestCase
{
    /** @test */
    public function traits_sets_up_relationship()
    {
        $model = Post::create();

        $this->assertNull($model->richTextBody);

        $richText = $model->richTextBody()->create([
            'field' => 'body',
            'body' => $this->content(),
        ]);

        $this->assertNotNull($model->refresh()->richTextBody);
        $this->assertTrue($model->richTextBody->is($richText));
        $this->assertInstanceOf(Content::class, $richText->body, 'Expected the body field on the RichText model to be cast to a Content instance, but it was not.');
        $this->assertTrue($model->richTextBody->record->is($model));
    }

    /** @test */
    public function forwards_attribute_mutators_and_accessors_to_relationship()
    {
        $post = $this->createPost();

        $this->assertInstanceOf(RichText::class, $post->body);
        $this->assertTrue($post->relationLoaded('richTextBody'), 'Expected the richTextBody relationship to be loaded, but it was not.');
        $this->assertNotEmpty($post->richTextBody->body->raw());
        $this->assertTrue($post->richTextBody->is($post->body));

        $this->assertTrue($post->body->exists, 'RichText model was not saved with the parent model.');

        $this->assertInstanceOf(RichText::class, $post->refresh()->body, 'RichText model reference was lost after the parent model was refreshed.');
        $this->assertNotEmpty($post->body->raw());
    }

    /** @test */
    public function fowards_calls_to_relationship()
    {
        $post = $this->createPost();

        $this->assertCount(1, $post->body->attachments());
        $this->assertEquals($this->content(), $post->body->toTrixHtml());
    }

    /** @test */
    public function renders_to_text()
    {
        $post = $this->createPost();

        $expectedRender = <<<HTML
        <div class="trix-content">
            <h1>Hey, there</h1>
        <figure class="attachment attachment--preview attachment--png">
            <img src="http://example.com/red-1.png" width="200" height="100">
            <figcaption class="attachment__caption">
                Captioned
            </figcaption>
        </figure>

        </div>

        HTML;

        $this->assertEquals($expectedRender, "$post->body");
        $this->assertEquals($expectedRender, "$post->richTextBody");

        $post->refresh();

        $this->assertEquals($expectedRender, "$post->body");
        $this->assertEquals($expectedRender, "$post->richTextBody");
    }

    /** @test */
    public function can_eager_load_rich_text_fields()
    {
        $this->createPost()->fresh();
        $this->createPost()->fresh();

        $this->assertEquals(2, Post::count());

        $queryCounts = 0;

        DB::listen(function () use (&$queryCounts) {
            $queryCounts++;
        });

        // Without eager loading, it will load each post individually (2 DB
        // calls) then, for each post, it will load each rich text field's
        // relationship on demand (2 extrac DB calls, 1 for each field).

        foreach (Post::query()->orderBy('id')->cursor() as $post) {
            $post->body && $post->notes;
        }

        $this->assertEquals(5, $queryCounts);

        return;

        $queryCounts = 0;

        // The Post model has 2 rich text fields, which means 2 relationships, so eager
        // loading all fields will result in one query for each relationship. Which,
        // for us, it means 1 query for the posts, and 1 for each relationship.

        Post::withRichText()->get()->each(fn ($post) => $post->body && $post->notes);
        $this->assertEquals(3, $queryCounts);

        $queryCounts = 0;

        // Eager loading only one field will only load that specific field's relationship...
        Post::withRichText('body')->get()->each(fn ($post) => $post->body);
        $this->assertEquals(2, $queryCounts);
    }

    /** @test */
    public function throws_exception_when_eager_loading_unkown_rich_text_field()
    {
        $this->expectException(RuntimeException::class);

        Post::withRichText(['unkown'])->get();
    }

    /** @test */
    public function can_have_different_fields_on_the_same_model()
    {
        $post = Post::create([
            'body' => '<h1>hello from body</h1>',
            'notes' => '<h1>hello from notes</h1>',
        ]);

        $expectedBodyContent = <<<HTML
        <div class="trix-content">
            <h1>hello from body</h1>
        </div>

        HTML;

        $expectedNotesContent = <<<HTML
        <div class="trix-content">
            <h1>hello from notes</h1>
        </div>

        HTML;

        $this->assertEquals($expectedNotesContent, "$post->notes");
        $this->assertEquals($expectedBodyContent, "$post->body");
        $this->assertEquals($expectedNotesContent, "$post->richTextNotes");
        $this->assertEquals($expectedBodyContent, "$post->richTextBody");

        $post->refresh();

        $this->assertEquals($expectedNotesContent, "$post->notes");
        $this->assertEquals($expectedBodyContent, "$post->body");
        $this->assertEquals($expectedNotesContent, "$post->richTextNotes");
        $this->assertEquals($expectedBodyContent, "$post->richTextBody");
    }

    private function createPost(): Post
    {
        return Post::create(['body' => $this->content()]);
    }

    private function content(): string
    {
        return <<<HTML
        <h1>Hey, there</h1>
        <figure data-trix-attachment='{"contentType":"image\/png","url":"http:\/\/example.com\/red-1.png","filename":"red-1.png","filesize":100,"width":200,"height":100}' data-trix-attributes='{"presentation":"gallery","caption":"Captioned"}'></figure>
        HTML;
    }
}
