<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\Post;
use Workbench\Database\Factories\PostFactory;

class ModelTest extends TestCase
{
    #[Test]
    public function converts_html(): void
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

    #[Test]
    public function plain_text(): void
    {
        $post = PostFactory::new()->create([
            'body' => '<h1>Hello World</h1>',
        ]);

        $this->assertEquals('Hello World', $post->refresh()->body->toPlainText());
    }

    #[Test]
    public function without_content(): void
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

    #[Test]
    public function with_blank_content(): void
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

    #[Test]
    public function updates_content(): void
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

    #[Test]
    public function touches_record_when_rich_text_is_updated(): void
    {
        $this->freezeTime();

        $post = PostFactory::new()->create([
            'body' => '<h1>Old Value</h1>',
        ])->fresh();

        $this->travel(5)->minutes();

        $post->update([
            'body' => '<h1>New Value</h1>',
        ]);

        $this->assertFalse($post->created_at->eq($post->updated_at), 'Didnt touch the record timestamps.');
    }

    #[Test]
    public function doesnt_touch_record_when_rich_text_isnt_updated(): void
    {
        $this->freezeTime();

        $post = PostFactory::new()->create([
            'body' => '<h1>Old Value</h1>',
        ])->fresh();

        $this->travel(5)->minutes();

        $post->update([
            'body' => '<h1>Old Value</h1>',
        ]);

        $this->assertTrue($post->created_at->eq($post->updated_at), 'Record timestamps were touched, but it shouldnt.');
    }

    #[Test]
    public function doesnt_touch_record_when_touching_is_disabled_on_the_specific_model(): void
    {
        $this->freezeTime();

        $post = PostFactory::new()->create([
            'body' => '<h1>Old Value</h1>',
        ])->fresh();

        $this->travel(5)->minutes();

        Post::withoutTouching(fn () => $post->update([
            'body' => '<h1>New Value</h1>',
        ]));

        $this->assertTrue($post->refresh()->created_at->eq($post->refresh()->updated_at), 'Record timestamps were touched, but it shouldnt.');
    }

    #[Test]
    public function doesnt_touch_record_when_touching_is_disabled_globally(): void
    {
        $this->freezeTime();

        $post = PostFactory::new()->create([
            'body' => '<h1>Old Value</h1>',
        ])->fresh();

        $this->travel(5)->minutes();

        Model::withoutTouching(fn () => $post->update([
            'body' => '<h1>New Value</h1>',
        ]));

        $this->assertTrue($post->refresh()->created_at->eq($post->refresh()->updated_at), 'Record timestamps were touched, but it shouldnt.');
    }
}
