<?php

namespace Tonysm\RichTextLaravel\Attachables;

use Illuminate\Database\Eloquent\Model;
use Tonysm\GlobalId\SignedGlobalId;

/**
 * @mixin AtachableContract
 */
trait Attachable
{
    public function richTextContentType(): string
    {
        return 'application/octet-stream';
    }

    public function toRichTextAttributes(array $attributes = []): array
    {
        return collect($attributes)
            ->replace([
                'sgid' => $this->richTextSgid(),
                'content_type' => $this->richTextContentType(),
            ])
            ->filter()
            ->all();
    }

    public function toTrixContent(): ?string
    {
        return $this->richTextRender();
    }

    public function richTextSgid(): string
    {
        return SignedGlobalId::create($this, [
            'for' => 'rich-text-laravel',
            'expires_at' => null,
        ])->toString();
    }

    public function equalsToAttachable(AttachableContract $attachable): bool
    {
        if ($this instanceof Model && $attachable instanceof Model) {
            return $this->is($attachable);
        }

        return $this->richTextRender() == $attachable->richTextRender();
    }
}
