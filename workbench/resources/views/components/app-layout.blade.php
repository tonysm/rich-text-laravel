@props(['margin' => 'my-10'])
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rich Text Laravel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree', 'ui-sans-serif', 'system-ui', 'sans-serif', "Apple Color Emoji",
                            "Segoe UI Emoji"
                        ],
                    }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @tailwind base;
        @tailwind components;
        @tailwind utilities;

        @layer base {
            .trix-content figure[data-trix-mutable=true] > :first-child {
                @apply ring-2 ring-offset-2;
            }

            .trix-content figure[data-trix-mutable=true]:where(:has(.oembed)) > :first-child {
                @apply rounded;
            }

            .trix-content figure[data-trix-mutable=true] img {
                @apply ring-0 outline-none;
            }
        }

        /* Restore semantic HTML defaults that Tailwind removes, for rich text editing */
        @layer components {
            .lexxy-editor__content {
                ul,
                ol {
                    list-style: revert;
                    margin: revert;
                    padding: revert;
                }        h1, h2, h3, h4, h5, h6 {
                    font-size: revert;
                    font-weight: revert;
                    margin: revert;
                }
                strong {
                    font-weight: revert;
                }
                em {
                    font-style: revert;
                }
                code[data-language] {
                    display: block;
                    border-radius: 6px;
                    background: #ccc;
                    margin: 1.5em 0;
                    padding: 1em;
                    font-size: 0.876rem;
                }

                blockquote {
                    border-left: 3px solid #ccc;
                    padding: 1em;
                }
            }

            summary.lexxy-editor__toolbar-button {
                list-style: none;
                align-content: center;
                text-align: center;
            }
        }
    </style>

    <x-rich-text::styles theme="richtextlaravel" />

    {{-- Tribute's Styles... --}}
    <link rel="stylesheet" href="https://unpkg.com/tributejs@5.1.3/dist/tribute.css">

    <style>
        [js-cloak] {
            display: none !important;
        }
    </style>

    <script type="importmap">
    {
        "imports": {
            "@37signals/lexxy": "https://ga.jspm.io/npm:@37signals/lexxy@0.7.0-beta/dist/lexxy.esm.js",
            "@hotwired/stimulus": "https://cdn.skypack.dev/@hotwired/stimulus",
            "tributejs": "https://ga.jspm.io/npm:tributejs@5.1.3/dist/tribute.min.js",
            "trix": "https://unpkg.com/trix@2.1.0/dist/trix.esm.min.js"
        },
        "scopes": {
            "https://ga.jspm.io/": {
                "@lexical/clipboard": "https://ga.jspm.io/npm:@lexical/clipboard@0.38.2/LexicalClipboard.dev.mjs",
                "@lexical/code": "https://ga.jspm.io/npm:@lexical/code@0.38.2/LexicalCode.dev.mjs",
                "@lexical/dragon": "https://ga.jspm.io/npm:@lexical/dragon@0.38.2/LexicalDragon.dev.mjs",
                "@lexical/extension": "https://ga.jspm.io/npm:@lexical/extension@0.38.2/LexicalExtension.dev.mjs",
                "@lexical/history": "https://ga.jspm.io/npm:@lexical/history@0.38.2/LexicalHistory.dev.mjs",
                "@lexical/html": "https://ga.jspm.io/npm:@lexical/html@0.38.2/LexicalHtml.dev.mjs",
                "@lexical/link": "https://ga.jspm.io/npm:@lexical/link@0.38.2/LexicalLink.dev.mjs",
                "@lexical/list": "https://ga.jspm.io/npm:@lexical/list@0.38.2/LexicalList.dev.mjs",
                "@lexical/markdown": "https://ga.jspm.io/npm:@lexical/markdown@0.38.2/LexicalMarkdown.dev.mjs",
                "@lexical/plain-text": "https://ga.jspm.io/npm:@lexical/plain-text@0.38.2/LexicalPlainText.dev.mjs",
                "@lexical/rich-text": "https://ga.jspm.io/npm:@lexical/rich-text@0.38.2/LexicalRichText.dev.mjs",
                "@lexical/selection": "https://ga.jspm.io/npm:@lexical/selection@0.38.2/LexicalSelection.dev.mjs",
                "@lexical/table": "https://ga.jspm.io/npm:@lexical/table@0.38.2/LexicalTable.dev.mjs",
                "@lexical/utils": "https://ga.jspm.io/npm:@lexical/utils@0.38.2/LexicalUtils.dev.mjs",
                "@rails/activestorage": "https://ga.jspm.io/npm:@rails/activestorage@7.2.300/app/assets/javascripts/activestorage.esm.js",
                "dompurify": "https://ga.jspm.io/npm:dompurify@3.3.1/dist/purify.es.mjs",
                "lexical": "https://ga.jspm.io/npm:lexical@0.38.2/Lexical.dev.mjs",
                "marked": "https://ga.jspm.io/npm:marked@16.4.2/lib/marked.esm.js",
                "prismjs": "https://ga.jspm.io/npm:prismjs@1.30.0/prism.js",
                "prismjs/components/prism-bash": "https://ga.jspm.io/npm:prismjs@1.30.0/components/prism-bash.js",
                "prismjs/components/prism-clike": "https://ga.jspm.io/npm:prismjs@1.30.0/components/prism-clike.js",
                "prismjs/components/": "https://ga.jspm.io/npm:prismjs@1.30.0/components/",
                "prismjs/components/prism-diff": "https://ga.jspm.io/npm:prismjs@1.30.0/components/prism-diff.js",
                "prismjs/components/prism-go": "https://ga.jspm.io/npm:prismjs@1.30.0/components/prism-go.js",
                "prismjs/components/prism-json": "https://ga.jspm.io/npm:prismjs@1.30.0/components/prism-json.js",
                "prismjs/components/prism-markup": "https://ga.jspm.io/npm:prismjs@1.30.0/components/prism-markup.js",
                "prismjs/components/prism-markup-templating": "https://ga.jspm.io/npm:prismjs@1.30.0/components/prism-markup-templating.js",
                "prismjs/components/prism-php": "https://ga.jspm.io/npm:prismjs@1.30.0/components/prism-php.js",
                "prismjs/components/prism-ruby": "https://ga.jspm.io/npm:prismjs@1.30.0/components/prism-ruby.js"
            }
        }
    }
    </script>

    {{-- Install Stimulus via CDN --}}
    <script type="module">
        import { Application, Controller } from '@hotwired/stimulus'
        import Tribute from 'tributejs'
        import Trix from 'trix'
        import * as Lexxy from "@37signals/lexxy"

        if ('configure' in Lexxy) {
            Lexxy.configure({
                global: {
                    attachmentTagName: "rich-text-attachment",
                },
            })
        } else {
            console.error('Lexxy object doesnt have a configure method...');
        }

        window.Stimulus = Application.start()

        class AutoCompleteManager {
            #tribute
            #element

            constructor(element) {
                this.#initializeTribute(element)
            }

            detach() {
                this.#tribute.detach(this.#element)
            }

            addMention({ sgid, content }) {
                const attachment = new Trix.Attachment({
                    sgid,
                    content,
                    contentType: 'application/vnd.rich-text-laravel.user-mention+html'
                })

                this.#editor.insertAttachment(attachment)
                this.#editor.insertString(" ")
            }

            #initializeTribute(element) {
                this.#tribute = new Tribute({
                    allowSpaces: true,
                    lookup: 'name',
                    values: this.#fetchUsers,
                })

                this.#tribute.attach(this.#element = element)
                this.#tribute.range.pasteHtml = this.#pasteHtml.bind(this)
            }

            #fetchUsers(text, callback) {
                fetch(`/mentions?search=${text}`, { headers: { Accept: 'application/json' } })
                    .then(resp => resp.json())
                    .then(users => callback(users))
                    .catch(error => callback([]))
            }

            #pasteHtml(html, startPosition, endPosition) {
                let range = this.#editor.getSelectedRange()
                let position = range[0]
                let length = endPosition - startPosition

                this.#editor.setSelectedRange([position - length, position])
                this.#editor.deleteInDirection('backward')
            }

            get #editor() {
                return this.#element.editor;
            }
        }

        class UploadManager {
            upload(event) {
                if (! event?.attachment?.file) return

                const form = new FormData()
                form.append('attachment', event.attachment.file)

                const options = {
                    method: 'POST',
                    body: form,
                    headers: {
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name=csrf-token]').content,
                    }
                }

                fetch('/attachments', options)
                    .then(resp => resp.json())
                    .then((data) => {
                        event.attachment.setAttributes({
                            url: data.image_url,
                            href: data.image_url,
                        })
                    })
            }
        }

        Stimulus.register("rich-text-uploader", class extends Controller {
            static values = { acceptFiles: Boolean }

            #uploader

            connect() {
                this.#uploader = new UploadManager()
            }

            upload(event) {
                if (! this.acceptFilesValue) {
                    event.preventDefault()
                    return
                }

                this.#uploader.upload(event)
            }
        })

        Stimulus.register("rich-text-mentions", class extends Controller {
            #autocompleter

            connect() {
                this.#autocompleter = new AutoCompleteManager(this.element)
            }

            addMention({ detail: { item: { original: mention }}}) {
                this.#autocompleter.addMention(mention)
            }
        })

        Stimulus.register("rich-text", class extends Controller {
            submitByKeyboard(event) {
                if (event.key === "Enter" && (event.metaKey || event.ctrlKey)) {
                    this.#submitMessage(event)
                }
            }

            #submitMessage(event) {
                event.preventDefault()

                if (this.element.textContent.trim().length > 0) {
                    this.element.closest("form").requestSubmit()
                }
            }
        })

        Stimulus.register("composer", class extends Controller {
            #submitByKeyboardEnabled = true

            static values = {
                showToolbar: { type: Boolean, default: false },
            }

            static targets = ["text"]

            rejectFiles(event) {
                event.preventDefault()
            }

            disableSubmitByKeyboard() {
                this.#submitByKeyboardEnabled = false
            }

            enableSubmitByKeyboard() {
                this.#submitByKeyboardEnabled = true
            }

            toggleToolbar() {
                this.showToolbarValue = ! this.showToolbarValue

                this.textTarget.focus()
            }

            submitByKeyboard(event) {
                if (! this.#submitByKeyboardEnabled) return;

                const metaEnter = event.key === "Enter" && (event.metaKey || event.ctrlKey)
                const plainEnter = event.keyCode == 13 && !event.shiftKey && !event.isComposing

                if (! this.#usingTouchDevice && (metaEnter || (plainEnter && ! this.#isToolbarVisible))) {
                    this.#submit(event)
                }
            }

            #submit(event) {
                event.preventDefault()

                if (this.textTarget.textContent.trim().length > 0) {
                    this.element.closest("form").requestSubmit();
                }
            }

            get #isToolbarVisible() {
                return this.showToolbarValue
            }

            get #usingTouchDevice() {
                return "ontouchstart" in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
            }
        })

        Stimulus.register("oembed", class extends Controller {
            static targets = ["text"]

            #abortController

            disconnect() {
                this.#abortController?.abort()
            }

            pasted(event) {
                const { range } = event.paste

                if (! range) return

                const url = this.#urlFromRange(range)

                if (! url) return

                this.#convertToLink(url, range)
                this.#processOembed(url)
            }

            #urlFromRange(range) {
                const document = this.#editor.getDocument()

                const string = document.getStringAtRange(range)
                const trimmed = string.trim()

                const startOffset = range[0] + string.indexOf(trimmed)
                const endOffset = startOffset + trimmed.length

                const url = document.getStringAtRange([startOffset, endOffset])

                if (! this.#isValidUrl(url)) {
                    return null;
                }

                return url;
            }

            #isValidUrl(url) {
                return /^(?:[a-z0-9]+:\/\/|www\.)[^\s]+$/.test(url)
            }

            #convertToLink(url, range) {
                const currentRange = this.#editor.getSelectedRange()

                this.#editor.setSelectedRange(range)
                this.#editor.recordUndoEntry("Convert Pasted URL to Link")
                this.#editor.activateAttribute('href', url)
                this.#editor.setSelectedRange(currentRange)
            }

            #processOembed(url) {
                this.#fetchOpengraphMetadata(url)
                    .then((response) => response.json())
                    .then(this.#insertOpengraphAttachment.bind(this))
                    .catch(() => null)
            }

            #fetchOpengraphMetadata(url) {
                this.#abortController?.abort()
                this.#abortController = new AbortController()

                return fetch('/opengraph-embeds', {
                    method: 'POST',
                    body: JSON.stringify({ url }),
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.head.querySelector('[name=csrf-token]').content,
                    },
                    signal: this.#abortController.signal,
                })
            }

            #insertOpengraphAttachment(attributes) {
                if (! this.#shouldInsertOpengraphAttachment(attributes.href)) return;

                const currentRange = this.#editor.getSelectedRange()

                this.#editor.setSelectedRange(this.#editor.getSelectedRange())
                this.#editor.recordUndoEntry("Insert Opengraph preview for Pasted URL")
                this.#editor.insertAttachment(new Trix.Attachment({
                    ...attributes,
                    caption: attributes.description,
                }))
                this.#editor.setSelectedRange(currentRange)
            }

            #shouldInsertOpengraphAttachment(url) {
                return this.#editor.getDocument().toString().includes(url)
            }

            get #editor() {
                return this.textTarget.editor
            }
        })

        // Simple cloak
        setTimeout(() => {
            [...document.querySelectorAll('[js-cloak]')].forEach(el => el.removeAttribute('js-cloak'))
        }, 0);
    </script>

    {{ $head ?? '' }}
</head>

<body class="bg-gray-100">
    <main {{ $attributes->merge(['class' => "max-w-3xl w-full mx-auto {$margin}"]) }}>
        {{ $slot }}
    </main
</body>
</html>
