@props(['id', 'name' => null, 'value' => ''])

@php
$editor = \Tonysm\RichTextLaravel\RichTextLaravel::editorName();
@endphp

<x-dynamic-component
    :component="'rich-text::' . $editor"
    :id="$id"
    :name="$name"
    :value="$value"
    {{ $attributes }}
>{{ $slot }}</x-dynamic-component>
