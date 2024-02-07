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
    {{ $attributes->merge(['class' => 'trix-content border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm prose max-w-none prose-pre:!bg-gray-400 prose-pre:!text-gray-900 prose-pre:!p-3']) }}
></trix-editor>
