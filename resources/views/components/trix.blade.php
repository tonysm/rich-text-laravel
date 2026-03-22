@props(['id', 'name' => null, 'value' => ''])

<div
    {{ $attributes->whereDoesntStartWith('wire:') }}
    @if ($attributes->has('wire:model'))
    x-data="{ content: $wire.entangle('{{ $attributes->wire('model')->value() }}') }"
    x-on:trix-initialize="$refs.input.value = content"
    x-on:trix-change="content = $refs.input.value"
    @endif
>
    <input
        type="hidden"
        @if ($name ?? false)
        name="{{ $name }}"
        @endif
        id="{{ $id }}_input"
        value="{{ $value }}"
        @if ($attributes->has('wire:model'))
        x-on:change="$refs.input.value = $event.target.value"
        {{ $attributes->whereStartsWith('wire:') }}
        @endif
    />

    <trix-toolbar
        id="{{ $id }}_toolbar"
        @if ($attributes->has('wire:model'))
        wire:ignore
        @endif
    ></trix-toolbar>

    <trix-editor
        id="{{ $id }}"
        toolbar="{{ $id }}_toolbar"
        input="{{ $id }}_input"
        class="trix-content"
        @if ($attributes->has('wire:model'))
        x-ref="input"
        wire:ignore
        @endif
    ></trix-editor>
</div>
