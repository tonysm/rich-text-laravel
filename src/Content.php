<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Collection;
use Tonysm\RichTextLaravel\Actions\ExtractAttachables;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;

class Content
{
    public static function fromStorage(string $content): static
    {
        return new static($content);
    }

    public static function toStorage(string $rawContent): string
    {
        return (new ParseAttachables)($rawContent, withAttachablesContent: false);
    }

    private function __construct(private string $content)
    {
    }

    public function attachables(): Collection
    {
        $result = collect();

        (new ExtractAttachables)($this->content, function (DOMElement $attachable) use ($result) {
            $result->add(AttachableFactory::fromAttachable($attachable));
        });

        return $result;
    }

    public function renderAttachables(): string
    {
        return (new ParseAttachables)($this->content, withAttachablesContent: true);
    }

    public function __toString()
    {
        return $this->renderAttachables();
    }
}
