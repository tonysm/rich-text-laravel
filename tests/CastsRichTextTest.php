<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Content;
use Tonysm\RichTextLaravel\Tests\Stubs\Post;

class CastsRichTextTest extends TestCase
{
    /** @test */
    public function parses_remote_image()
    {
        $remoteImageAttachment = [
            'contentType' => 'image/jpeg',
            'url' => 'http://example.com/image.jpg',
            'width' => 300,
        ];

        $serialized = urlencode(json_encode($remoteImageAttachment));

        /** @var \Tonysm\RichTextLaravel\Tests\Stubs\Post $post */
        $post = Post::create([
            'body' => <<<HTML
            <div>
                <figure data-trix-attachment="{$serialized}">
                    This should be removed...
                </figure>
            </div>
            HTML,
        ]);

        $rawParsedContent = $post->getRawOriginal('body');

        $this->assertStringNotContainsString('<figure', $rawParsedContent);
        $this->assertStringContainsString('<rich-text-attachable sgid=', $rawParsedContent);

        $this->assertInstanceOf(Content::class, $post->refresh()->body);

        $this->assertStringContainsString("<figure", (string) $post->body);
    }
}
