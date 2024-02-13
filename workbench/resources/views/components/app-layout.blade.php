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

    {{-- Install Stimulus via CDN --}}
    <script type="module">
        import {
            Application,
            Controller
        } from 'https://cdn.skypack.dev/@hotwired/stimulus'
        import Tribute from 'https://ga.jspm.io/npm:tributejs@5.1.3/dist/tribute.min.js'
        import Trix from 'https://ga.jspm.io/npm:trix@2.0.10/dist/trix.esm.min.js'

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

        Stimulus.register('rich-text', class extends Controller {
            #autocompleter
            #uploader

            connect() {
                this.#autocompleter = new AutoCompleteManager(this.element)
                this.#uploader = new UploadManager()
            }

            disconnect() {
                this.#autocompleter.detach()
            }

            addMention({ detail: { item: { original: mention }}}) {
                this.#autocompleter.addMention(mention)
            }

            upload(event) {
                this.#uploader.upload(event)
            }
        })
    </script>
</head>

<body class="bg-gray-100">
    <main class="max-w-3xl w-full mx-auto my-10" {{ $attributes ?? '' }}>
        {{ $slot }}
    </main
</body>
</html>
