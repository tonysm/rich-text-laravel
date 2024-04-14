<?php

namespace Workbench\App\Models\Opengraph;

use DOMElement;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;

class OpengraphEmbed implements AttachableContract
{
    use OpengraphEmbed\Fetching;

    const ATTRIBUTES = ['title', 'url', 'image', 'description'];

    const CONTENT_TYPE = 'application/vnd.rich-text-laravel.opengraph-embed';

    public static function fromNode(DOMElement $node): ?OpengraphEmbed
    {
        if ($node->hasAttribute('content-type') && $node->getAttribute('content-type') === static::CONTENT_TYPE) {
            return new OpengraphEmbed(...static::attributesFromNode($node));
        }

        return null;
    }

    public static function tryFromAttributes(array $attributes)
    {
        if (validator($attributes, [
            'title' => ['required'],
            'url' => ['required'],
            'description' => ['required'],
            'image' => ['sometimes', 'required', 'url'],
        ])->fails()) {
            return null;
        }

        return new static(
            $attributes['url'],
            $attributes['image'] ?? null,
            $attributes['title'],
            $attributes['description'],
        );
    }

    private static function attributesFromNode(DOMElement $node): array
    {
        return [
            'href' => $node->getAttribute('href'),
            'url' => $node->getAttribute('url'),
            'filename' => $node->getAttribute('filename'),
            'description' => $node->getAttribute('caption'),
        ];
    }

    public function __construct(
        public $href,
        public $url,
        public $filename,
        public $description,
    ) {
    }

    public function toRichTextAttributes(array $attributes): array
    {
        return collect($attributes)
            ->replace([
                'content_type' => $this->richTextContentType(),
                'previewable' => true,
            ])
            ->filter()
            ->all();
    }

    public function equalsToAttachable(AttachableContract $attachable): bool
    {
        return $this->richTextRender() === $attachable->richTextRender();
    }

    public function richTextRender(array $options = []): string
    {
        return view('rich-text-laravel.attachables.opengraph_embed', [
            'attachable' => $this,
        ])->render();
    }

    public function richTextAsPlainText(?string $caption = null): string
    {
        return '';
    }

    public function richTextContentType(): string
    {
        return static::CONTENT_TYPE;
    }

    public function toArray(): array
    {
        return [
            'href' => $this->href,
            'url' => $this->url,
            'filename' => $this->filename,
            'description' => $this->description,
            'contentType' => $this->richTextContentType(),
            'content' => $this->richTextRender(),
        ];
    }
}
