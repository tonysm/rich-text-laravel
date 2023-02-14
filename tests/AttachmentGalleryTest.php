<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\AttachmentGallery;
use Tonysm\RichTextLaravel\HtmlConversion;

class AttachmentGalleryTest extends TestCase
{
    /** @test */
    public function gets_attachment()
    {
        $document = HtmlConversion::document(<<<'HTML'
        <rich-text-attachment presentation="gallery" url="http://example.com/blue.png" content-type="image/png" width="300" height="300" filename="blue.png" filesize="200"></rich-text-attachment>
        <rich-text-attachment presentation="gallery" url="http://example.com/red.png" content-type="image/png" width="300" height="300" filename="red.png" filesize="200"></rich-text-attachment>
        HTML);

        $gallery = new AttachmentGallery($document->firstChild);

        $this->assertCount(2, $gallery->attachments());
        $this->assertEquals(2, $gallery->count());

        $this->assertEquals('blue.png', $gallery->attachments()->first()->attachable->filename);
        $this->assertEquals('red.png', $gallery->attachments()->last()->attachable->filename);
    }
}
