<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Attachables\MissingAttachable;
use Tonysm\RichTextLaravel\Attachables\RemoteImage;
use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\Content;
use Tonysm\RichTextLaravel\Tests\Stubs\User;

class ContentTest extends TestCase
{
    /** @test */
    public function equality()
    {
        $html = "<div>test</div>";
        $content = $this->fromHtml($html);

        $this->assertStringContainsString($html, $content->toHtml());
    }

    /** @test */
    public function keeps_newlines_consistent()
    {
        $html = "<div>a<br></div>";
        $content = $this->fromHtml($html);

        $this->assertStringContainsString($html, $content->toHtml());
    }

    /** @test */
    public function extracts_links()
    {
        $html = '<a href="http://example.com/1">first link</a><br><a href="http://example.com/1">second link</a>';
        $content = $this->fromHtml($html);

        $this->assertEquals(['http://example.com/1'], $content->links());
    }

    /** @test */
    public function extracts_attachables()
    {
        $attachable = User::create(['name' => 'Jon Doe']);
        $sgid = $attachable->richTextSgid();

        $html = <<<HTML
        <rich-text-attachment sgid="$sgid" caption="Captioned"></rich-text-attachment>
        HTML;

        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachments());

        $attachment = $content->attachments()->first();

        $this->assertEquals("Captioned", $attachment->caption());
        $this->assertTrue($attachment->attachable->is($attachable));
    }

    /** @test */
    public function extracts_remote_image_attachables()
    {
        $html = <<<HTML
        <rich-text-attachment content-type="image" url="http://example.com/cat.jpg" width="200" height="100" caption="Captioned"></rich-text-attachment>
        HTML;
        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachments());

        $attachment = $content->attachments()->first();
        $this->assertEquals('Captioned', $attachment->caption());

        $attachable = $attachment->attachable;
        $this->assertInstanceOf(RemoteImage::class, $attachable);
        $this->assertEquals('http://example.com/cat.jpg', $attachable->url);
        $this->assertEquals('200', $attachable->width);
        $this->assertEquals('100', $attachable->height);
    }

    /** @test */
    public function handles_destryed_attachables_as_missing()
    {
        $attachable = User::create(['name' => 'Jon Doe']);
        $sgid = $attachable->richTextSgid();
        $html = <<<HTML
        <rich-text-attachment sgid="$sgid" caption="User mention"></rich-text-attachment>
        HTML;

        $attachable->delete();

        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachments());
        $this->assertInstanceOf(MissingAttachable::class, $content->attachments()->first()->attachable);
    }

    /** @test */
    public function extracts_missing_attachables()
    {
        $html = <<<HTML
        <rich-text-attachment sgid="missing" caption="Captioned"></rich-text-attachment>
        HTML;

        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachments());
        $this->assertInstanceOf(MissingAttachable::class, $content->attachments()->first()->attachable);
    }

    /** @test */
    public function converts_trix_formatted_attachments()
    {
        $html = <<<HTML
        <figure
            data-trix-attachment='{"sgid": "123", "contentType": "text/plain", "width": 200, "height": 100}'
            data-trix-attributes='{"caption": "Captioned"}'
        ></figure>
        HTML;

        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachments());

        $this->assertStringContainsString('<rich-text-attachment sgid="123" content-type="text/plain" width="200" height="100" caption="Captioned"></rich-text-attachment>', $content->toHtml());
    }

    /** @test */
    public function converts_trix_formatetd_attachments_with_custom_tag_name()
    {
        $this->withAttachmentTagName('arbitrary-tag', function () {
            $html = <<<HTML
            <figure
                data-trix-attachment='{"sgid": "123", "contentType": "text/plain", "width": 200, "height": 100}'
                data-trix-attributes='{"caption": "Captioned"}'
            ></figure>
            HTML;

            $content = $this->fromHtml($html);

            $this->assertCount(1, $content->attachments());

            $this->assertStringContainsString('<arbitrary-tag sgid="123" content-type="text/plain" width="200" height="100" caption="Captioned"></arbitrary-tag>', $content->toHtml());
        });
    }

    /** @test */
    public function ignores_trix_formatteed_attachments_with_bad_json()
    {
        $html = <<<HTML
        <div data-trix-attachment='{"sgid": "pure garbate...}'></div>
        HTML;

        $content = $this->fromHtml($html);

        $this->assertCount(0, $content->attachments());
    }

    /** @test */
    public function minifies_attachment_markup()
    {
        $attachmentHtml = '<rich-text-attachment sgid="1"><div>HTML</div></rich-text-attachment>';
        $this->assertStringContainsString('<div>HTML</div>', $this->fromHtml($attachmentHtml)->toHtml());
    }

    /** @test */
    public function canonicalizes_attachment_gallery_markup()
    {
        $attachmentHtml = '<rich-text-attachment sgid="1" presentation="gallery"></rich-text-attachment><rich-text-attachment sgid="2" presentation="galerry"></rich-text-attachment>';
        $html = sprintf('<div class="attachment-gallery attachment-gallery--2">%s</div>', $attachmentHtml);
        $this->assertStringContainsString($attachmentHtml, $this->fromHtml($html)->toHtml());
    }

    /** @test */
    public function canonicalizes_attachment_gallery_markup_with_whitespaces()
    {
        $attachmentHtml = '<action-text-attachment sgid="1" presentation="gallery"></action-text-attachment><action-text-attachment sgid="2" presentation="gallery"></action-text-attachment>';
        $html = sprintf('<blockquote><div class="attachment-gallery attachment-gallery--2">%s</div></blockquote>', $attachmentHtml);
        $this->assertStringContainsString($attachmentHtml, $this->fromHtml($html)->toHtml());
    }

    /** @test */
    public function canonicalizes_with_layout()
    {
        $attachmentHtml = '<rich-text-attachment sgid="1" presentation="gallery"></rich-text-attachment><rich-text-attachment sgid="2" presentation="galerry"></rich-text-attachment>';
        $html = sprintf('<div class="attachment-gallery attachment-gallery--2">%s</div>', $attachmentHtml);
        $this->assertStringContainsString($attachmentHtml, $this->fromHtml($html)->toHtml());
    }

    /** @test */
    public function renders_to_trix_hmtl_with_model_attachments()
    {
        $user = UserWithCustomRenderContent::create(['name' => 'Hey There']);
        $sgid = $user->richTextSgid();
        $attachmentHtml = <<<HTML
        <div>Hey, <rich-text-attachment sgid="$sgid" content-type="application/octet-stream"></rich-text-attachment></div>
        HTML;

        $content = $this->fromHtml($attachmentHtml);

        $this->assertEquals(
            <<<HTML
            <div>Hey, <figure data-trix-attachment='{"sgid":"$sgid","contentType":"application\/octet-stream","content":"&lt;span&gt;$user->name&lt;\/span&gt;"}'></figure></div>
            HTML,
            $content->toTrixHtml()
        );
    }

    /** @test */
    public function renders_to_trix_html_with_image_attachments()
    {
        $attachmentHtml = <<<HTML
        <div>Hey, <rich-text-attachment content-type="image/png" url="http://localhost/blue.png" filename="blue.png" filesize="1168" width="300" height="300" previewable="true" presentation="gallery"></rich-text-attachment></div>
        HTML;

        $content = $this->fromHtml($attachmentHtml);

        $this->assertEquals(
            <<<HTML
            <div>Hey, <figure data-trix-attachment='{"contentType":"image\/png","url":"http:\/\/localhost\/blue.png","filename":"blue.png","filesize":1168,"width":300,"height":300,"previewable":true}' data-trix-attributes='{"presentation":"gallery"}'></figure></div>
            HTML,
            $content->toTrixHtml()
        );
    }

    private function withAttachmentTagName(string $tagName, callable $callback)
    {
        try {
            $oldTagName = Attachment::$TAG_NAME;
            Attachment::useTagName($tagName);
            $callback();
        } finally {
            Attachment::useTagName($oldTagName);
        }
    }

    private function fromHtml(string $html): Content
    {
        return tap(new Content($html), fn (Content $content) => $this->assertNotEmpty($content->toHtml()));
    }
}

class UserWithCustomRenderContent extends User
{
    protected $table = 'users';

    public function richTextRender($content = null, array $options = []): string
    {
        return "<span>{$this->name}</span>";
    }
}
