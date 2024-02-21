<input type="hidden" id="create_message_input" name="content" value="{{ old('content', '') }}" />

<trix-editor
    placeholder="Say something nice..."
    data-composer-target="text"
    data-controller="rich-text-mentions"
    data-action="keydown->composer#submitByKeyboard tribute-replaced->rich-text-mentions#addMention tribute-active-true->composer#disableSubmitByKeyboard tribute-active-false->composer#enableSubmitByKeyboard trix-attachment-add->composer#rejectFiles"
    id="create_message"
    name="content"
    toolbar="create_message_toolbar"
    input="create_message_input"
    class="trix-content overflow-auto rounded-0 p-0 [&_pre]:text-sm min-h-0 max-h-[12vh] border-0 sm:group-data-[composer-show-toolbar-value=true]:py-2 sm:group-data-[composer-show-toolbar-value=true]:min-h-[4em]"
></trix-editor>

<trix-toolbar js-cloak id="create_message_toolbar" class="[&_.trix-button-group]:!mb-0 sm:group-data-[composer-show-toolbar-value=true]:mt-2">
    <div class="trix-button-row !hidden sm:group-data-[composer-show-toolbar-value=true]:!inline-block">
        <span class="trix-button-group trix-button-group--text-tools" data-trix-button-group="text-tools">
            <button type="button" class="trix-button trix-button--icon trix-button--icon-bold" data-trix-attribute="bold" data-trix-key="b" title="Bold" tabindex="-1">Bold</button>
            <button type="button" class="trix-button trix-button--icon trix-button--icon-italic" data-trix-attribute="italic" data-trix-key="i" title="Italic" tabindex="-1">Italic</button>
            <button type="button" class="trix-button trix-button--icon trix-button--icon-link" data-trix-attribute="href" data-trix-action="link" data-trix-key="k" title="Link" tabindex="-1" data-trix-active="">Link</button>
            <button type="button" class="trix-button trix-button--icon trix-button--icon-quote" data-trix-attribute="quote" title="Quote" tabindex="-1">Quote</button>
            <button type="button" class="trix-button trix-button--icon trix-button--icon-code" data-trix-attribute="code" title="Code" tabindex="-1">Code</button>
        </span>

        <span hidden class="hidden" data-trix-button-group="history-tools">
            <button type="button" class="hidden" data-trix-action="undo" data-trix-key="z" title="Undo" tabindex="-1" disabled="">Undo</button>
            <button type="button" class="hidden" data-trix-action="redo" data-trix-key="shift+z" title="Redo" tabindex="-1" disabled="">Redo</button>
        </span>
    </div>

    <div class="trix-dialogs" data-trix-dialogs>
        <div class="trix-dialog trix-dialog--link bottom-[3em] !top-auto" data-trix-dialog="href" data-trix-dialog-attribute="href">
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
