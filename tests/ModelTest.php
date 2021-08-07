<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Tests\Stubs\Post;

class ModelTest extends TestCase
{
    /** @test */
    public function converts_html()
    {
        $post = Post::create([
            'content' => '<h1>Hello World</h1>',
        ]);

        $this->assertEquals(<<<HTML
        <div class="trix-content">
            <h1>Hello World</h1>
        </div>

        HTML, "{$post->refresh()->content}");
    }

    /** @test */
    public function plain_text()
    {
        $post = Post::create([
            'content' => '<h1>Hello World</h1>',
        ]);

        $this->assertEquals('Hello World', $post->refresh()->content->toPlainText());
    }

    /** @test */
    public function without_content()
    {
        $post = Post::create([]);

        $this->assertEquals(<<<HTML
        <div class="trix-content">
            </div>

        HTML, "{$post->content}");

        $this->assertTrue($post->content->isEmpty());
    }

    /** @test */
    public function with_blank_content()
    {
        $post = Post::create([
            'content' => '',
        ]);

        $this->assertEquals(<<<HTML
        <div class="trix-content">
            </div>

        HTML, "{$post->content}");

        $this->assertTrue($post->content->isEmpty());
    }

    /** @test */
    public function updates_content()
    {
        $post = Post::create([
            'content' => '<h1>Old Value</h1>',
        ]);

        $post->refresh()->update([
            'content' => '<h1>New Value</h1>',
        ]);

        $this->assertEquals(<<<HTML
        <div class="trix-content">
            <h1>New Value</h1>
        </div>

        HTML, "{$post->refresh()->content}");
    }
}
