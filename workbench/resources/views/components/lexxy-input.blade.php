@props(['id', 'name', 'value' => '', 'acceptFiles' => true, 'mentions' => true, 'mentionsUrl' => null])

<div class="w-full bg-white border border-gray-300 relative border-gray-300 px-2 focus-within:ring-1 focus-within:border-indigo-500 focus-within:ring-indigo-500 rounded-md shadow-sm">
    <lexxy-editor {{ $attributes->merge(['id' => $id, 'name' => $name, 'value' => $value]) }}>
        @if ($mentions ?? false)
            <lexxy-prompt trigger="@" name="mention" src="{{ $mentionsUrl ?? route('mentions.index') }}"></lexxy-prompt>
        @endif

        <lexxy-prompt trigger="#" insert-editable-text>
            <lexxy-prompt-item search="First">
                <template type="menu">First</template>
                <template type="editor">
                    <a href="#first">#First</a>
                </template>
            </lexxy-prompt-item>
            <lexxy-prompt-item search="Second">
                <template type="menu">Second</template>
                <template type="editor">
                    <a href="#second">#Second</a>
                </template>
            </lexxy-prompt-item>
        </lexxy-prompt>

        <lexxy-prompt trigger="$" insert-editable-text>
            <lexxy-prompt-item search="First">
                <template type="menu">First</template>
                <template type="editor">
                    <a href="#first">#First</a>
                </template>
                <template type="editor">
                    <a href="#second">#Second</a>
                </template>
            </lexxy-prompt-item>
            <lexxy-prompt-item search="Second">
                <template type="menu">Second</template>
                <template type="editor">
                    <a href="#third">#Third</a>
                </template>
                <template type="editor">
                    <a href="#fourth">#Fourth</a>
                </template>
            </lexxy-prompt-item>
        </lexxy-prompt>

        {{ $slot }}
    </lexxy-editor>
</div>

