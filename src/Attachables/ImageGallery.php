<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMElement;

class ImageGallery implements AttachableContract
{
    use Attachable;

    public static function fromNode(array $attachmentData, DOMElement $attachment): ?AttachableContract
    {
        return new ImageGallery($attachmentData);
    }

    public function __construct(public array $attachables)
    {
    }

    public function richTextRender(): string
    {
        return view('rich-text-laravel::attachables._image_gallery', [
            'attachables' => $this->attachables,
        ])->render();
    }
}
