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

    public static function fromNode(array $attachmentData, DOMElement $attachment): ?AttachableContract
    {
        if ($attachmentData['url'] ?? false && str_starts_with($attachmentData['content-type'] ?? '', 'image/')) {
            return new static($attachmentData);
        }

        return null;
    }

    public function __construct(array $data)
    {
        $this->url = $data['url'];
        $this->contentType = $data['contentType'];
        $this->width = $data['width'];
        $this->height = $data['height'] ?? '';
        $this->caption = $data['caption'] ?? '';
    }

    public function toSgid(): string
    {
        return base64_encode(encrypt(serialize($this)));
    }

    public function render(): string
    {
        return view('rich-text-laravel::attachables._remote_image', [
            'remoteImage' => $this,
        ])->render();
    }
}
