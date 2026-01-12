@props(['id', 'name', 'value' => '', 'acceptFiles' => true])

<div class="w-full bg-white border border-gray-300 relative border-gray-300 px-2 focus-within:ring-1 focus-within:border-indigo-500 focus-within:ring-indigo-500 rounded-md shadow-sm">
    <lexxy-editor {{ $attributes->merge(['id' => $id, 'name' => $name, 'value' => str_replace('rich-text-attachment', 'action-text-attachment', Tonysm\RichTextLaravel\Fragment::wrap($value ?? '')->replace(Tonysm\RichTextLaravel\Attachment::$SELECTOR, function (DOMElement $node) {
        if (! $node->hasAttribute('url')) {
            $node->setAttribute('content', match (true) {
                $node->hasAttribute('content') => json_encode($node->getAttribute('content')),
                default => '',
            });
        }

        return $node;
    })), 'class' => 'prose max-w-none']) }}></lexxy-editor>
</div>

