<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use DOMXPath;
use Illuminate\Support\Collection;

class AttachmentGallery
{
    const TAG_NAME = 'div';

    private $cachedAttachments;

    public static function fromNode(DOMElement $node): static
    {
        return new static($node);
    }

    public static function attachmentSelector(): string
    {
        return sprintf(
            '%s[@presentation="gallery"]',
            Attachment::$TAG_NAME,
        );
    }

    public static function selector(): string
    {
        return sprintf(
            '//%s[count(%s) > 1]',
            static::TAG_NAME,
            static::attachmentSelector(),
        );
    }

    public function __construct(public DOMElement $node)
    {
    }

    public function attachments(): Collection
    {
        return $this->cachedAttachments ??= $this->computeAttachments();
    }

    public function count(): int
    {
        return $this->attachments()->count();
    }

    public function richTextRender(): string
    {
        return view('rich-text-laravel::attachment_galleries._attachment_gallery', [
            'attachmentGallery' => $this,
        ])->render();
    }

    private function computeAttachments(): Collection
    {
        $xpath = new DOMXPath($this->node->ownerDocument);
        $attachmentNodes = $xpath->query(static::attachmentSelector(), $this->node);
        $result = collect();

        if ($attachmentNodes === false) {
            return $result;
        }

        foreach ($attachmentNodes as $node) {
            $result->add(Attachment::fromNode($node)->withFullAttributes());
        }

        return $result;
    }
}
