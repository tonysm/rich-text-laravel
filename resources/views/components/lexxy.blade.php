@props(['id', 'name' => null, 'value' => ''])

<div
    {{ $attributes->whereDoesntStartWith('wire:') }}
    @if ($attributes->has('wire:model'))
    x-data="{ content: $wire.entangle('{{ $attributes->wire('model')->value() }}') }"
    x-on:lexxy:initialize="$nextTick(() => $refs.input.value = content)"
    x-on:lexxy:change="content = $refs.input.value"
    @endif
>
    <lexxy-editor
        id="{{ $id }}"
        class="lexxy-content"
        value="{{ $value }}"
        @if ($name ?? false)
        name="{{ $name }}"
        @endif
        @if ($attributes->has('wire:model'))
        wire:ignore
        x-ref="input"
        @endif
    >{{ $slot }}</lexxy-editor>
</div>
