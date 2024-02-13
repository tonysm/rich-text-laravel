@props(['id', 'name', 'value' => '', 'toolbar' => null, 'acceptFiles' => true])

<div class="w-full bg-white border border-gray-300 relative border-gray-300 p-2 focus-within:ring-1 focus-within:border-indigo-500 focus-within:ring-indigo-500 rounded-md shadow-sm [&_trix-toolbar]:sticky [&_trix-toolbar]:top-0 [&_trix-toolbar]:-mx-2 [&_trix-toolbar]:px-4 [&_trix-toolbar]:bg-white [&_trix-toolbar]:border-b [&_trix-toolbar]:shadow-sm [&_trix-toolbar]:py-2 [&_trix-toolbar]:z-10">
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $id }}_input"
        value="{{ $value }}"
    />

    @if ($toolbar === 'minimal')
    <trix-toolbar id="{{ $id . '_toolbar_' . $toolbar }}">
        <div class="trix-button-row">
            <span class="trix-button-group trix-button-group--text-tools" data-trix-button-group="text-tools">
                <button type="button" class="trix-button trix-button--icon trix-button--icon-bold" data-trix-attribute="bold" data-trix-key="b" title="Bold" tabindex="-1">Bold</button>
                <button type="button" class="trix-button trix-button--icon trix-button--icon-italic" data-trix-attribute="italic" data-trix-key="i" title="Italic" tabindex="-1">Italic</button>
                <button type="button" class="trix-button trix-button--icon trix-button--icon-link" data-trix-attribute="href" data-trix-action="link" data-trix-key="k" title="Link" tabindex="-1" data-trix-active="">Link</button>
            </span>

            <span hidden class="hidden" data-trix-button-group="history-tools">
                <button type="button" class="hidden" data-trix-action="undo" data-trix-key="z" title="Undo" tabindex="-1" disabled="">Undo</button>
                <button type="button" class="hidden" data-trix-action="redo" data-trix-key="shift+z" title="Redo" tabindex="-1" disabled="">Redo</button>
            </span>
        </div>

        <div class="trix-dialogs" data-trix-dialogs>
            <div class="trix-dialog trix-dialog--link" data-trix-dialog="href" data-trix-dialog-attribute="href">
                <div class="trix-dialog__link-fields">
                    <input type="url" name="href" class="trix-input trix-input--dialog" placeholder="{{ __('Enter a URL...') }}" aria-label="URL" required data-trix-input>

                    <div class="trix-button-group">
                        <input type="button" class="trix-button trix-button--dialog" value="{{ __('Link') }}" data-trix-method="setAttribute">
                        <input type="button" class="trix-button trix-button--dialog" value="{{ __('Unlink') }}" data-trix-method="removeAttribute">
                    </div>
                </div>
            </div>
        </div>
    </trix-toolbar>
    @endif

    <trix-editor {{ $attributes->merge(array_filter([
        'id' => $id,
        'toolbar' => $toolbar ? $id . '_toolbar_' . $toolbar : null,
        'class' => 'trix-content w-full ring-0 outline-none border-0 px-1 pb-0 pt-2 !shadow-none',
        'input' => "{$id}_input",
        'data-controller' => 'rich-text',
        'data-rich-text-accept-files-value' => $acceptFiles ? 'true' : 'false',
        'data-action' => 'tribute-replaced->rich-text#addMention trix-attachment-add->rich-text#upload',
    ])) }}></trix-editor>
</div>
