<?php

namespace Tonysm\RichTextLaravel\Tests;

use Tonysm\RichTextLaravel\Attachables\RemoteImage;
use Tonysm\RichTextLaravel\Content;

class ContentTest extends TestCase
{
    /** @test */
    public function can_get_all_attachables()
    {
        $sgid = ($remoteImage = new RemoteImage([
            'url' => 'https://lorempixel.com/image.jpg',
            'contentType' => 'image/jpeg',
            'width' => 300,
        ]))->toSgid();

        $content = Content::fromStorage(<<<HTML
        <div>
            <rich-text-attachable sgid="{$sgid}"></rich-text-attachable>
        </div>
        HTML);

        $this->assertCount(1, $content->attachables());
        $this->assertInstanceOf(RemoteImage::class, $content->attachables()[0]);

        $this->assertEquals($remoteImage->url, $content->attachables()[0]->url);
        $this->assertEquals($remoteImage->contentType, $content->attachables()[0]->contentType);
        $this->assertEquals($remoteImage->width, $content->attachables()[0]->width);
    }
}
