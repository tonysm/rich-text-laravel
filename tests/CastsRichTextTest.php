<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Content;
use Tonysm\RichTextLaravel\Tests\Stubs\Post;
use Tonysm\RichTextLaravel\Tests\Stubs\User;

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
        $this->assertStringNotContainsString('This should be removed', $rawParsedContent);
        $this->assertStringContainsString('<rich-text-attachable sgid=', $rawParsedContent);

        $this->assertInstanceOf(Content::class, $post->refresh()->body);

        $this->assertStringContainsString("<figure", (string) $post->body);
    }

    /** @test */
    public function can_handle_attachments_with_sgid()
    {
        /** @var User $user */
        $user = User::create([
            'name' => 'User',
        ]);

        $serialized = urlencode(json_encode([
            'sgid' => $user->toRichTextSgid(),
        ]));

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
        $this->assertStringNotContainsString($user->name, $rawParsedContent);
        $this->assertStringContainsString('<rich-text-attachable sgid=', $rawParsedContent);
        $this->assertStringNotContainsString('data-trix-attachment=', $rawParsedContent);

        $this->assertInstanceOf(Content::class, $post->refresh()->body);

        $this->assertStringContainsString($user->name, (string) $post->body);
        $this->assertStringNotContainsString('sgid=', (string) $post->body);
        $this->assertStringContainsString('data-trix-attachment=', (string) $post->body);
    }

    /** @test */
    public function can_handle_no_attachments()
    {
        /** @var \Tonysm\RichTextLaravel\Tests\Stubs\Post $post */
        $post = Post::create([
            'body' => <<<HTML
            <div>this has no attachments</div>
            HTML,
        ]);

        $rawParsedContent = $post->getRawOriginal('body');

        $this->assertStringNotContainsString('<rich-text-attachable sgid=', $rawParsedContent);

        $this->assertInstanceOf(Content::class, $post->refresh()->body);

        $this->assertStringContainsString('<div>this has no attachments</div>', (string) $post->body);
    }
}
