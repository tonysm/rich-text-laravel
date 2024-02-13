@props(['id', 'name', 'value' => ''])

<div class="w-full bg-white border border-gray-300 relative border-gray-300 p-2 focus-within:ring-1 focus-within:border-indigo-500 focus-within:ring-indigo-500 rounded-md shadow-sm [&_trix-toolbar]:sticky [&_trix-toolbar]:top-0 [&_trix-toolbar]:-mx-2 [&_trix-toolbar]:px-4 [&_trix-toolbar]:bg-white [&_trix-toolbar]:border-b [&_trix-toolbar]:shadow-sm [&_trix-toolbar]:py-2 [&_trix-toolbar]:z-10">
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $id }}_input"
        value="{{ $value }}"
    />

    <trix-editor {{ $attributes->merge([
        'id' => $id,
        'class' => 'trix-content w-full ring-0 outline-none border-0 px-1 pb-0 pt-2 !shadow-none',
        'input' => "{$id}_input",
        'data-controller' => 'rich-text',
        'data-action' => 'tribute-replaced->rich-text#addMention trix-attachment-add->rich-text#upload'
    ]) }}></trix-editor>
</div>
