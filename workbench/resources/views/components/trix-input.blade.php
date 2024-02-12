@props(['id', 'name', 'value' => ''])

<input
    type="hidden"
    name="{{ $name }}"
    id="{{ $id }}_input"
    value="{{ $value }}"
/>

<trix-editor
    id="{{ $id }}"
    input="{{ $id }}_input"
    {{ $attributes->merge(['class' => 'trix-content bg-white border-gray-300 focus:ring-1 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}
    data-controller="rich-text"
    data-action="tribute-replaced->rich-text#addMention trix-attachment-add->rich-text#upload"
></trix-editor>
