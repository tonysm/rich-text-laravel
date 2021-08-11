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

    /** @test */
    public function gets_gallery_attachments()
    {
        $content = $this->fromHtml(<<<HTML
        <div>
            <h1>Hey there</h1>
            <div class="this will be removed">
                <rich-text-attachment presentation="gallery" url="http://example.com/red-1.png" filename="red-1.png" filesize="200" content-type="image/png" width="300" height="300" caption="hey there"></rich-text-attachment>
                <rich-text-attachment presentation="gallery" url="http://example.com/blue-1.png" filename="blue-1.png" filezie="200" content-type="image/png" width="300" height="300" caption="hey there"></rich-text-attachment>
            </div>
            <div class="this will be removed">
                <rich-text-attachment presentation="gallery" url="http://example.com/red-2.png" filename="red-2.png" filesize="200" content-type="image/png" width="300" height="300" caption="hey there"></rich-text-attachment>
                <rich-text-attachment presentation="gallery" url="http://example.com/blue-2.png" filename="blue-2.png" filezie="200" content-type="image/png" width="300" height="300" caption="hey there"></rich-text-attachment>
                <rich-text-attachment presentation="gallery" url="http://example.com/green-2.png" filename="green-2.png" filezie="200" content-type="image/png" width="300" height="300" caption="hey there"></rich-text-attachment>
            </div>
        </div>
        HTML);

        $attachmentGalleries = $content->attachmentGalleries();

        $this->assertCount(2, $attachmentGalleries, 'Could not find all attachment galleries.');

        // First gallery attachments...
        $this->assertCount(2, $attachmentGalleries->first()->attachments(), 'Failed to cast only attachment nodes to attachments.');
        $this->assertEquals('red-1.png', $attachmentGalleries->first()->attachments()->first()->attachable->filename);
        $this->assertEquals('blue-1.png', $attachmentGalleries->first()->attachments()->last()->attachable->filename);

        // Second gallery attachments...
        $this->assertCount(3, $attachmentGalleries->last()->attachments());
        $this->assertEquals('red-2.png', $attachmentGalleries->last()->attachments()->first()->attachable->filename);
        $this->assertEquals('blue-2.png', $attachmentGalleries->last()->attachments()->skip(1)->first()->attachable->filename);
        $this->assertEquals('green-2.png', $attachmentGalleries->last()->attachments()->last()->attachable->filename);
    }

    /** @test */
    public function gets_only_attachments_of_galleries()
    {
        $content = $this->fromHtml(<<<HTML
        <div>
            <h1>Hey there</h1>
            <rich-text-attachment presentation="gallery" url="http://example.com/white.png" filename="white.png" filesize="1" content-type="image/png" width="200" height="200" caption="should not get"></rich-text-attachment>
            <div class="this will be removed">
                <rich-text-attachment presentation="gallery" url="http://example.com/red-1.png" filename="red-1.png" filesize="200" content-type="image/png" width="300" height="300" caption="hey there"></rich-text-attachment>
                <rich-text-attachment presentation="gallery" url="http://example.com/blue-1.png" filename="blue-1.png" filezie="200" content-type="image/png" width="300" height="300" caption="hey there"></rich-text-attachment>
            </div>
            <div class="this will be removed">
                <rich-text-attachment presentation="gallery" url="http://example.com/red-2.png" filename="red-2.png" filesize="200" content-type="image/png" width="300" height="300" caption="hey there"></rich-text-attachment>
                <rich-text-attachment presentation="gallery" url="http://example.com/blue-2.png" filename="blue-2.png" filezie="200" content-type="image/png" width="300" height="300" caption="hey there"></rich-text-attachment>
                <rich-text-attachment presentation="gallery" url="http://example.com/green-2.png" filename="green-2.png" filezie="200" content-type="image/png" width="300" height="300" caption="hey there"></rich-text-attachment>
            </div>
        </div>
        HTML);

        $galleryAttachments = $content->galleryAttachments();

        $this->assertCount(5, $galleryAttachments, 'Could not find all gallery attachments.');

        $this->assertEquals('red-1.png', $galleryAttachments[0]->attachable->filename);
        $this->assertEquals('blue-1.png', $galleryAttachments[1]->attachable->filename);
        $this->assertEquals('red-2.png', $galleryAttachments[2]->attachable->filename);
        $this->assertEquals('blue-2.png', $galleryAttachments[3]->attachable->filename);
        $this->assertEquals('green-2.png', $galleryAttachments[4]->attachable->filename);
    }

    /** @test */
    public function canonicalizes_attachment_galleries()
    {
        $content = $this->fromHtml(<<<HTML
        <div>
            <h1>Hey there</h1>
            <figure
                data-trix-attachment='{"sgid": "123", "contentType": "text/plain", "width": 200, "height": 100}'
                data-trix-attributes='{"presentation": "gallery", "caption": "Captioned"}'
            ></figure>

            <div class="this will be removed">
                <figure
                    data-trix-attachment='{"contentType": "text/png", "width": 200, "height": 100, "url": "http://example.com/red-1.png", "filename": "red-1.png", "filesize": 100}'
                    data-trix-attributes='{"presentation": "gallery", "caption": "Captioned"}'
                ></figure>
                <figure
                    data-trix-attachment='{"contentType": "text/png", "width": 200, "height": 100, "url": "http://example.com/blue-1.png", "filename": "blue-1.png", "filesize": 100}'
                    data-trix-attributes='{"presentation": "gallery", "caption": "Captioned"}'
                ></figure>
            </div>
            <div class="this will be removed">
                <figure
                    data-trix-attachment='{"contentType": "text/png", "width": 200, "height": 100, "url": "http://example.com/red-1.png", "filename": "red-1.png", "filesize": 100}'
                    data-trix-attributes='{"presentation": "gallery", "caption": "Captioned"}'
                ></figure>
                <figure
                    data-trix-attachment='{"contentType": "text/png", "width": 200, "height": 100, "url": "http://example.com/blue-1.png", "filename": "blue-1.png", "filesize": 100}'
                    data-trix-attributes='{"presentation": "gallery", "caption": "Captioned"}'
                ></figure>
                <figure
                    data-trix-attachment='{"contentType": "text/png", "width": 200, "height": 100, "url": "http://example.com/green-1.png", "filename": "green-1.png", "filesize": 100}'
                    data-trix-attributes='{"presentation": "gallery", "caption": "Captioned"}'
                ></figure>
            </div>
        </div>
        HTML);

        $this->assertStringNotContainsString('this will be removed', $content->raw());
        $this->assertCount(2, $content->attachmentGalleries());
        $this->assertCount(5, $content->galleryAttachments());
    }

    /** @test */
    public function renders_galleries()
    {
        $content = $this->fromHtml(<<<HTML
        <div>
            <h1>Hey there</h1>
            <figure
                data-trix-attachment='{"contentType": "image/png", "width": 200, "height": 100, "url": "http://example.com/red-1.png", "filename": "red-1.png", "filesize": 100}'
                data-trix-attributes='{"presentation": "gallery", "caption": "Captioned"}'
            ></figure>

            <div class="this will be removed">
                <figure
                    data-trix-attachment='{"contentType": "image/png", "width": 200, "height": 100, "url": "http://example.com/red-1.png", "filename": "red-1.png", "filesize": 100}'
                    data-trix-attributes='{"presentation": "gallery", "caption": "Captioned"}'
                ></figure>
                <figure
                    data-trix-attachment='{"contentType": "image/png", "width": 200, "height": 100, "url": "http://example.com/blue-1.png", "filename": "blue-1.png", "filesize": 100}'
                    data-trix-attributes='{"presentation": "gallery", "caption": "Captioned"}'
                ></figure>
            </div>
        </div>
        HTML);

        // The markup indentation looks a bit off, but that's fine...

        $this->assertEquals(<<<HTML
        <div>
            <h1>Hey there</h1>
            <rich-text-attachment content-type="image/png" width="200" height="100" url="http://example.com/red-1.png" filename="red-1.png" filesize="100" presentation="gallery" caption="Captioned"></rich-text-attachment>

            <div class="attachment-gallery attachment-gallery--2">
            <figure class="attachment attachment--preview attachment--png">
            <img src="http://example.com/red-1.png" width="200" height="100">
            <figcaption class="attachment__caption">
                Captioned
            </figcaption>
        </figure>

            <figure class="attachment attachment--preview attachment--png">
            <img src="http://example.com/blue-1.png" width="200" height="100">
            <figcaption class="attachment__caption">
                Captioned
            </figcaption>
        </figure>

        </div>
        </div>
        HTML, $content->renderWithAttachments());
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
