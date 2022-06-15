@props(['id', 'name', 'value' => ''])

<input
    type="hidden"
    name="{{ $name }}"
    id="{{ $id }}_input"
    value="{{ $value }}"
    {{ $attributes->except(['class', 'style', 'toolbar']) }}
/>

<trix-editor
    id="{{ $id }}"
    input="{{ $id }}_input"
    {{ $attributes->merge(['class' => 'trix-content']) }}
></trix-editor>
