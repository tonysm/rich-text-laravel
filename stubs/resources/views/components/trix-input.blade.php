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
    {{ $attributes->merge(['class' => 'trix-content border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm prose max-w-none dark:prose-strong:text-white dark:prose-blockquote:text-white dark:prose-h1:text-white dark:prose-a:text-white prose-pre:!bg-gray-400 prose-pre:!text-gray-900 prose-pre:!p-3 dark:prose-pre:!bg-gray-800 dark:prose-pre:!text-gray-200']) }}
></trix-editor>
