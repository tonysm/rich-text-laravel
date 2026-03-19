@props(['id', 'name', 'value' => ''])

<lexxy-editor
    {{ $attributes->merge(['id' => $id, 'name' => $name, 'value' => $value, 'class' => 'lexxy-content']) }}
>{{ $slot }}</lexxy-editor>
