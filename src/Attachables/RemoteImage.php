<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMElement;

class RemoteImage implements AttachableContract
{
    use Attachable;

    public $url;
    public $contentType;
    public $width;
    public $height;
    public $caption;

    public static function fromNode(array $attachmentData, array $trixAttributes, DOMElement $attachment): ?AttachableContract
    {
        if ($attachmentData['url'] ?? false && str_starts_with($attachmentData['content-type'] ?? '', 'image/')) {
            return new static($attachmentData, $trixAttributes);
        }

        return null;
    }

    public function __construct(array $data, array $trixAttributes)
    {
        $this->url = $data['url'];
        $this->contentType = $data['contentType'];
        $this->width = $data['width'];
        $this->height = $data['height'] ?? '';
        $this->caption = $trixAttributes['caption'] ?? '';
    }

    public function richTextRender(): string
    {
        return view('rich-text-laravel::attachables._remote_image', [
            'remoteImage' => $this,
        ])->render();
    }
}
