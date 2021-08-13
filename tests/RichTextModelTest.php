<?php

namespace Tonysm\RichTextLaravel\Tests;

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
