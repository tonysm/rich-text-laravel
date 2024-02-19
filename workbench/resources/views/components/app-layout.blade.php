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

    <x-rich-text::styles />

    {{-- Tribute's Styles... --}}
    <link rel="stylesheet" href="https://unpkg.com/tributejs@5.1.3/dist/tribute.css">

    <style>
        [js-cloak] {
            display: none !important;
        }
    </style>

    {{-- Install Stimulus via CDN --}}
    <script type="module">
        import {
            Application,
            Controller
        } from 'https://cdn.skypack.dev/@hotwired/stimulus'
        import Tribute from 'https://ga.jspm.io/npm:tributejs@5.1.3/dist/tribute.min.js'
        import Trix from 'https://ga.jspm.io/npm:trix@2.0.10/dist/trix.esm.min.js'

        [...document.querySelectorAll('[js-cloak]')].forEach(el => el.removeAttribute('js-cloak'))

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
                fetch(`/mentions?search=${text}`)
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
    </script>
</head>

<body class="bg-gray-100">
    <main {{ $attributes->merge(['class' => "max-w-3xl w-full mx-auto {$margin}"]) }}>
        {{ $slot }}
    </main
</body>
</html>
