<?php

namespace Tonysm\RichTextLaravel\Tests;

use Workbench\Database\Factories\PostFactory;

class ModelTest extends TestCase
{
    /** @test */
    public function converts_html()
    {
        $post = PostFactory::new()->create([
            'body' => '<h1>Hello World</h1>',
        ]);

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
            <h1>Hello World</h1>
        </div>

        HTML, "{$post->refresh()->body}");
    }

    /** @test */
    public function plain_text()
    {
        $post = PostFactory::new()->create([
            'body' => '<h1>Hello World</h1>',
        ]);

        $this->assertEquals('Hello World', $post->refresh()->body->toPlainText());
    }

    /** @test */
    public function without_content()
    {
        $post = PostFactory::new()->create();
        $post->richTextBody->delete();
        $post->refresh();

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
        </div>

        HTML, "{$post->body}");

        $this->assertTrue($post->body->isEmpty());
    }

    /** @test */
    public function with_blank_content()
    {
        $post = PostFactory::new()->create([
            'body' => '',
        ]);

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
        </div>

        HTML, "{$post->body}");

        $this->assertTrue($post->body->isEmpty());
    }

    /** @test */
    public function updates_content()
    {
        $post = PostFactory::new()->create([
            'body' => '<h1>Old Value</h1>',
        ]);

        $post->refresh()->update([
            'body' => '<h1>New Value</h1>',
        ]);

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
            <h1>New Value</h1>
        </div>

        HTML, "{$post->refresh()->body}");
    }
}
