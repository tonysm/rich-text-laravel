<?php

namespace Tonysm\RichTextLaravel\Tests;

use Illuminate\Support\Facades\Log;
use Tonysm\RichTextLaravel\Attachables\ContentAttachment;
use Tonysm\RichTextLaravel\Attachables\MissingAttachable;
use Tonysm\RichTextLaravel\Attachables\RemoteImage;
use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\Content;
use Workbench\App\Models\Opengraph\OpengraphEmbed;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

class ContentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->parse('2021-08-23T02:05:59+00:00'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function equality(): void
    {
        $html = '<div>test</div>';
        $content = $this->fromHtml($html);

        $this->assertStringContainsString($html, $content->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function keeps_newlines_consistent(): void
    {
        $html = '<div>a<br></div>';
        $content = $this->fromHtml($html);

        $this->assertStringContainsString($html, $content->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function handles_emojis(): void
    {
        $html = '<div>Emojis 🎉🎉🎉</div>';
        $content = $this->fromHtml($html);

        $this->assertStringContainsString($html, $content->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function extracts_links(): void
    {
        $html = '<a href="http://example.com/1">first link</a><br><a href="http://example.com/1">second link</a>';
        $content = $this->fromHtml($html);

        $this->assertEquals(['http://example.com/1'], $content->links()->all());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function extracts_attachments(): void
    {
        $attachable = UserFactory::new()->create(['name' => 'Jon Doe']);
        $sgid = $attachable->richTextSgid();

        $html = <<<HTML
        <rich-text-attachment sgid="$sgid" caption="Captioned"></rich-text-attachment>
        HTML;

        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachments());

        $attachment = $content->attachments()->first();

        $this->assertEquals('Captioned', $attachment->caption());
        $this->assertTrue($attachment->attachable->is($attachable));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function extracts_attachables(): void
    {
        $attachable = UserFactory::new()->create(['name' => 'Jon Doe']);
        $sgid = $attachable->richTextSgid();

        $html = <<<HTML
        <rich-text-attachment sgid="$sgid" trix-attributes="{'caption': 'Captioned"></rich-text-attachment>
        HTML;

        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachables());

        $extractedAttachable = $content->attachables()->first();

        $this->assertNotNull($extractedAttachable);
        $this->assertTrue($extractedAttachable->is($attachable));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function extracts_remote_image_attachables(): void
    {
        $html = <<<'HTML'
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function handles_destryed_attachables_as_missing(): void
    {
        $attachable = UserFactory::new()->create(['name' => 'Jon Doe']);
        $sgid = $attachable->richTextSgid();
        $html = <<<HTML
        <rich-text-attachment sgid="$sgid" caption="User mention"></rich-text-attachment>
        HTML;

        $attachable->delete();

        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachments());
        $this->assertInstanceOf(MissingAttachable::class, $content->attachments()->first()->attachable);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function extracts_missing_attachables(): void
    {
        $html = <<<'HTML'
        <rich-text-attachment sgid="missing" caption="Captioned"></rich-text-attachment>
        HTML;

        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachments());
        $this->assertInstanceOf(MissingAttachable::class, $content->attachments()->first()->attachable);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function converts_trix_formatted_attachments(): void
    {
        $html = <<<'HTML'
        <figure
            data-trix-attachment='{"sgid": "123", "contentType": "text/plain", "width": 200, "height": 100}'
            data-trix-attributes='{"caption": "Captioned"}'
        ></figure>
        HTML;

        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachments());

        $this->assertStringContainsString('<rich-text-attachment sgid="123" content-type="text/plain" width="200" height="100" caption="Captioned"></rich-text-attachment>', $content->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function converts_non_image_attachments(): void
    {
        $html = <<<'HTML'
        <div>
            <figure
                data-trix-attachment='{"contentType": "text/csv", "filename": "Test.csv", "filesize": "65"}'
                data-trix-content-type="text/csv"
                class="attachment attachment--file attachment--csv"
            >
                <figcaption class="attachment__caption">
                    <span class="attachment__name">Test.csv</span> <span class="attachment__size">65 Bytes</span>
                </figcaption>
            </figure>
        </div>
        HTML;

        $content = $this->fromHtml($html);

        $this->assertCount(1, $content->attachments());

        $this->assertStringContainsString('<rich-text-attachment content-type="text/csv" filename="Test.csv" filesize="65"></rich-text-attachment>', $content->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function converts_trix_formatetd_attachments_with_custom_tag_name(): void
    {
        $this->withAttachmentTagName('arbitrary-tag', function (): void {
            $html = <<<'HTML'
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function ignores_trix_formatteed_attachments_with_bad_json(): void
    {
        Log::shouldReceive('notice')->once();

        $html = <<<'HTML'
        <div data-trix-attachment='{"sgid": "pure garbate...}'></div>
        HTML;

        $content = $this->fromHtml($html);

        $this->assertCount(0, $content->attachments());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function minifies_attachment_markup(): void
    {
        $attachmentHtml = '<rich-text-attachment sgid="1"><div>HTML</div></rich-text-attachment>';
        $this->assertStringContainsString('<div>HTML</div>', $this->fromHtml($attachmentHtml)->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function canonicalizes_attachment_gallery_markup(): void
    {
        $attachmentHtml = '<rich-text-attachment sgid="1" presentation="gallery"></rich-text-attachment><rich-text-attachment sgid="2" presentation="galerry"></rich-text-attachment>';
        $html = sprintf('<div class="attachment-gallery attachment-gallery--2">%s</div>', $attachmentHtml);
        $this->assertStringContainsString($attachmentHtml, $this->fromHtml($html)->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function canonicalizes_attachment_gallery_markup_with_whitespaces(): void
    {
        $attachmentHtml = '<action-text-attachment sgid="1" presentation="gallery"></action-text-attachment><action-text-attachment sgid="2" presentation="gallery"></action-text-attachment>';
        $html = sprintf('<blockquote><div class="attachment-gallery attachment-gallery--2">%s</div></blockquote>', $attachmentHtml);
        $this->assertStringContainsString($attachmentHtml, $this->fromHtml($html)->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function canonicalizes_with_layout(): void
    {
        $attachmentHtml = '<rich-text-attachment sgid="1" presentation="gallery"></rich-text-attachment><rich-text-attachment sgid="2" presentation="galerry"></rich-text-attachment>';
        $html = sprintf('<div class="attachment-gallery attachment-gallery--2">%s</div>', $attachmentHtml);
        $this->assertStringContainsString($attachmentHtml, $this->fromHtml($html)->toHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_to_trix_hmtl_with_model_attachments(): void
    {
        $user = UserWithCustomRenderContent::create(UserFactory::new()->raw(['name' => 'Hey There']));
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_to_trix_html_with_image_attachments(): void
    {
        $attachmentHtml = <<<'HTML'
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function gets_gallery_attachments(): void
    {
        $content = $this->fromHtml(<<<'HTML'
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function gets_only_attachments_of_galleries(): void
    {
        $content = $this->fromHtml(<<<'HTML'
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function canonicalizes_attachment_galleries(): void
    {
        $content = $this->fromHtml(<<<'HTML'
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_galleries(): void
    {
        $content = $this->fromHtml(<<<'HTML'
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

        $this->assertEquals(<<<'HTML'
        <div>
            <h1>Hey there</h1>
            <rich-text-attachment content-type="image/png" width="200" height="100" url="http://example.com/red-1.png" filename="red-1.png" filesize="100" presentation="gallery" caption="Captioned"></rich-text-attachment>

            <div class="attachment-gallery attachment-gallery--2">
            <figure class="not-prose attachment attachment--preview attachment--png">
            <img src="http://example.com/red-1.png" width="200" height="100">
            <figcaption class="attachment__caption">
                Captioned
            </figcaption>
        </figure>

            <figure class="not-prose attachment attachment--preview attachment--png">
            <img src="http://example.com/blue-1.png" width="200" height="100">
            <figcaption class="attachment__caption">
                Captioned
            </figcaption>
        </figure>

        </div>
        </div>
        HTML, $content->renderWithAttachments());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_galleries_to_trix_html(): void
    {
        $content = $this->fromHtml(<<<'HTML'
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
            <figure data-trix-attachment='{"contentType":"image\/png","url":"http:\/\/example.com\/red-1.png","filename":"red-1.png","filesize":100,"width":200,"height":100}' data-trix-attributes='{"presentation":"gallery","caption":"Captioned"}'></figure>

            <div>
                <figure data-trix-attachment='{"contentType":"image\/png","url":"http:\/\/example.com\/red-1.png","filename":"red-1.png","filesize":100,"width":200,"height":100}' data-trix-attributes='{"presentation":"gallery","caption":"Captioned"}'></figure>
                <figure data-trix-attachment='{"contentType":"image\/png","url":"http:\/\/example.com\/blue-1.png","filename":"blue-1.png","filesize":100,"width":200,"height":100}' data-trix-attributes='{"presentation":"gallery","caption":"Captioned"}'></figure>
            </div>
        </div>
        HTML, $content->toTrixHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_file_attachments(): void
    {
        $content = $this->fromHtml(<<<'HTML'
        <div>
            <h1>Hey there</h1>
            <figure
                data-trix-attachment='{"contentType": "text/csv", "url": "http://example.com/test.csv", "filename": "test.csv", "filesize": 100}'
                data-trix-attributes='{}'
            ></figure>
        </div>
        HTML);

        // The markup indentation looks a bit off, but that's fine...

        $this->assertEquals(<<<HTML
        <div>
            <h1>Hey there</h1>
            <figure data-trix-attachment='{"contentType":"text\/csv","url":"http:\/\/example.com\/test.csv","filename":"test.csv","filesize":100}'></figure>
        </div>
        HTML, $content->toTrixHtml());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_html_content_attachment(): void
    {
        $attachment = $this->attachmentFromHtml('<rich-text-attachment content-type="text/html" content="abc"></rich-text-attachment>');
        $attachable = $attachment->attachable;

        $this->assertInstanceOf(ContentAttachment::class, $attachable);
        $this->assertEquals('text/html', $attachable->contentType);
        $this->assertEquals('abc', $attachable->content);

        $trixAttachment = $attachment->toTrixAttachment();
        $this->assertEquals('text/html', $trixAttachment->attributes()['contentType']);
        $this->assertEquals('abc', $trixAttachment->attributes()['content']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renders_content_attachment(): void
    {
        $attachment = $this->attachmentFromHtml('<rich-text-attachment content-type="text/html" content="&lt;p&gt;abc&lt;/p&gt;"></rich-text-attachment>');
        /** @var ContentAttachment $attachable */
        $attachable = $attachment->attachable;

        $this->assertEquals('<p>abc</p>', $attachable->renderTrixContentAttachment());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function supports_custom_content_attachments_without_sgid(): void
    {
        $contentType = OpengraphEmbed::CONTENT_TYPE;

        $content = $this->fromHtml(<<<HTML
        <div>
            Testing out with cards: <a href="https://github.com/tonysm/rich-text-laravel">https://github.com/tonysm/rich-text-laravel</a>
            <rich-text-attachment
                caption="Integrates the Trix Editor with Laravel. Inspired by the Action Text gem from Rails. - tonysm/rich-text-laravel"
                content-type="{$contentType}"
                filename="GitHub - tonysm/rich-text-laravel: Integrates the Trix Editor with Laravel. Inspired by the Action Text gem from Rails."
                href="https://github.com/tonysm/rich-text-laravel"
                url="https://opengraph.githubassets.com/7e956bd233205f222790d8cdbdadfc401886aabdc88a8fd0cfb3c7dcca44d635/tonysm/rich-text-laravel"
            ></rich-text-attachment>
        </div>
        HTML);

        $this->assertCount(1, $content->attachments());
        $this->assertCount(1, $content->attachables());
        $this->assertInstanceOf(OpengraphEmbed::class, $content->attachables()->first());
        $this->assertEquals('https://github.com/tonysm/rich-text-laravel', $content->attachables()->first()->href);
    }

    private function withAttachmentTagName(string $tagName, callable $callback): void
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

    private function attachmentFromHtml(string $html): Attachment
    {
        return $this->fromHtml($html)->attachments()->first();
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
