<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tonysm\RichTextLaravel\Content;
use Tonysm\RichTextLaravel\Exceptions\RichTextException;
use Tonysm\RichTextLaravel\Models\RichText;
use Workbench\App\Models\Post;
use Workbench\Database\Factories\PostFactory;

class RichTextModelTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function traits_sets_up_relationship(): void
    {
        $model = PostFactory::new()->create();

        $this->assertNotNull($richText = $model->richTextBody);

        $this->assertNotNull($model->refresh()->richTextBody);
        $this->assertTrue($model->richTextBody->is($richText));
        $this->assertInstanceOf(Content::class, $richText->body, 'Expected the body field on the RichText model to be cast to a Content instance, but it was not.');
        $this->assertTrue($model->richTextBody->record->is($model));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forwards_attribute_mutators_and_accessors_to_relationship(): void
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function fowards_calls_to_relationship(): void
    {
        $post = $this->createPost();

        $this->assertCount(1, $post->body->attachments());
        $this->assertEquals($this->content(), $post->body->toTrixHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_to_text(): void
    {
        $post = $this->createPost();

        $expectedRender = <<<'HTML'
        <div class="trix-content">
            <h1>Hey, there</h1>
        <figure class="not-prose attachment attachment--preview attachment--png">
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_eager_load_rich_text_fields(): void
    {
        PostWithNotes::truncate();

        $this->createPost(
            body: '<p>this is the body</p>',
            notes: '<p>this is the notes</p>',
        )->fresh();

        $this->createPost(
            body: '<p>this is the body</p>',
            notes: '<p>this is the notes</p>',
        )->fresh();

        $this->assertEquals(2, PostWithNotes::count());

        // Without eager loading, it will load each post individually (2 DB
        // calls) then, for each post, it will load each rich text field's
        // relationship on demand (2 extrac DB calls, 1 for each field).

        $queryCounts = 0;

        DB::listen(function () use (&$queryCounts): void {
            $queryCounts++;
        });

        foreach (PostWithNotes::query()->orderBy('id')->cursor() as $post) {
            $post->body && $post->notes;
        }

        $this->assertEquals(5, $queryCounts);

        $queryCounts = 0;

        // The Post model has 2 rich text fields, which means 2 relationships, so eager
        // loading all fields will result in one query for each relationship. Which,
        // for us, it means 1 query for the posts, and 1 for each relationship.

        PostWithNotes::withRichText()->get()->each(fn ($post): bool => $post->body && $post->notes);
        $this->assertEquals(3, $queryCounts);

        $queryCounts = 0;

        // Eager loading only one field will only load that specific field's relationship...
        PostWithNotes::withRichText('body')->get()->each(fn ($post) => $post->body);
        $this->assertEquals(2, $queryCounts);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function throws_exception_when_eager_loading_unkown_rich_text_field(): void
    {
        $this->expectException(RichTextException::class);

        PostWithNotes::withRichText(['unknown'])->get();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_have_different_fields_on_the_same_model(): void
    {
        $post = PostWithNotes::create(PostFactory::new()->raw([
            'body' => '<h1>hello from body</h1>',
            'notes' => '<h1>hello from notes</h1>',
        ]));

        $expectedBodyContent = <<<'HTML'
        <div class="trix-content">
            <h1>hello from body</h1>
        </div>

        HTML;

        $expectedNotesContent = <<<'HTML'
        <div class="trix-content">
            <h1>hello from notes</h1>
        </div>

        HTML;

        $this->assertEquals($expectedBodyContent, "$post->body");
        $this->assertEquals($expectedNotesContent, "$post->notes");
        $this->assertEquals($expectedBodyContent, "$post->richTextBody");
        $this->assertEquals($expectedNotesContent, "$post->richTextNotes");

        $post = $post->fresh();

        $this->assertEquals($expectedBodyContent, "$post->body");
        $this->assertEquals($expectedNotesContent, "$post->notes");
        $this->assertEquals($expectedBodyContent, "$post->richTextBody");
        $this->assertEquals($expectedNotesContent, "$post->richTextNotes");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_update_content(): void
    {
        $post = $this->createPost(
            body: '<p>this is the old body</p>',
        );

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
            <p>this is the old body</p>
        </div>

        HTML, "{$post->body}");

        $post->body = '<p>Hey</p>';
        $post->save();

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
            <p>Hey</p>
        </div>

        HTML, "{$post->body}");

        $post = $post->fresh();

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
            <p>Hey</p>
        </div>

        HTML, "{$post->body}");

        $post->body = '<p>Changed 2</p>';
        $post->save();

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
            <p>Changed 2</p>
        </div>

        HTML, "{$post->body}");

        $post = $post->fresh();

        $this->assertEquals(<<<'HTML'
        <div class="trix-content">
            <p>Changed 2</p>
        </div>

        HTML, "{$post->body}");
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_delete_attachments(): void
    {
        Storage::fake('public');

        $firstImage = '/images/attachments/post-01.png';
        $secondImage = '/images/attachments/post-02.png';

        Storage::disk('public')->put($firstImage, '');
        Storage::disk('public')->put($secondImage, '');

        $encodeImage = fn (string $imageUrl) => e($imageUrl);

        $post = PostForDeleting::create([
            'title' => 'Has attachments',
            'body' => <<<HTML
            <div>
                <figure data-trix-attachment='{"contentType":"image\/jpeg","url":"{$encodeImage($firstImage)}","href":"{$encodeImage($firstImage)}","filename":"first-image.jpg","filesize":47665,"width":880,"height":660}' data-trix-attributes='{"presentation":"gallery","caption":"First Image"}'></figure>
                <figure data-trix-attachment='{"contentType":"image\/jpeg","url":"{$encodeImage($secondImage)}","href":"{$encodeImage($secondImage)}","filename":"first-image.jpg","filesize":47665,"width":880,"height":660}' data-trix-attributes='{"presentation":"gallery","caption":"First Image"}'></figure>
            </div>
            HTML,
        ]);

        $post->delete();

        Storage::disk('public')->assertMissing($firstImage);
        Storage::disk('public')->assertMissing($secondImage);
    }

    private function createPost(?string $body = null, ?string $notes = null): PostWithNotes
    {
        return PostWithNotes::create(PostFactory::new()->raw([
            'body' => $body ?: $this->content(),
            'notes' => $notes ?: '',
        ]));
    }

    private function content(): string
    {
        return <<<HTML
        <h1>Hey, there</h1>
        <figure data-trix-attachment='{"contentType":"image\/png","url":"http:\/\/example.com\/red-1.png","filename":"red-1.png","filesize":100,"width":200,"height":100}' data-trix-attributes='{"presentation":"gallery","caption":"Captioned"}'></figure>
        HTML;
    }
}

class PostWithNotes extends Post
{
    protected $table = 'posts';

    protected $richTextAttributes = [
        'body',
        'notes',
    ];
}

class PostForDeleting extends Post
{
    protected $table = 'posts';

    protected $richTextAttributes = [
        'body',
    ];

    public static function booted(): void
    {
        static::deleted(function (Post $post) {
            foreach ($post->body->attachments() as $attachment) {
                Storage::disk('public')->delete($attachment->attachable->url);
            }
        });
    }
}
