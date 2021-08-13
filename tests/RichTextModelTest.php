<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Content;
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
    }

    private function content(): string
    {
        return <<<HTML
        <h1>Hey, there</h1>
        <figure
            data-trix-attachment='{"contentType": "image/png", "width": 200, "height": 100, "url": "http://example.com/red-1.png", "filename": "red-1.png", "filesize": 100}'
            data-trix-attributes='{"presentation": "gallery", "caption": "Captioned"}'
        ></figure>
        HTML;
    }
}
