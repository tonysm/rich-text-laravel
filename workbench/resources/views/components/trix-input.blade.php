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
    {{ $attributes->merge(['class' => 'trix-content bg-white border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm prose max-w-none prose-pre:!bg-gray-400 prose-pre:!text-gray-900 prose-pre:!p-3 prose-figure:m-0']) }}
    data-controller="rich-text"
    data-action="tribute-replaced->rich-text#addMention"
></trix-editor>
