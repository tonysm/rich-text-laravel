## Rich Text Laravel

- Rich Text Laravel integrates rich text editors like Trix and Lexxy with Laravel, inspired by Action Text from Rails.
- It stores rich text content in a canonical format using `<rich-text-attachment>` tags, decoupled from any specific editor.
- It provides two storage strategies: a dedicated `rich_texts` table (recommended) or storing directly on the model's own table via `'attribute' => true`.
- Models use the `HasRichText` trait and define rich text fields in the `$richTextAttributes` property.
- Each rich text field creates a dynamic relationship (`richText{FieldName}`) and a virtual attribute that forwards get/set to the relationship.
- Rich text fields can be eager loaded with `Model::withRichText()`, `withRichText('field')`, or `withRichText(['field1', 'field2'])`.
- Rich text content can be encrypted at-rest using the `'encrypted' => true` option, which uses Laravel's encryption.
- The `Content` object supports `render()`, `toPlainText()`, `toMarkdown()`, `toEditorHtml()`, `attachments()`, `links()`, `attachmentGalleries()`, and `galleryAttachments()`.
- Rendering for end users (`{!! $post->body !!}`) differs from rendering for editors (`$post->body->toEditorHtml()`).
- Content attachments use the `AttachableContract` interface and `Attachable` trait; models must implement `richTextRender(array $options): string`.
- Attachables are referenced via Signed Global IDs (SGIDs) from the GlobalID Laravel package.
- Custom attachments without SGIDs can be registered via `RichTextLaravel::withCustomAttachables()`.
- The editor abstraction uses the `Editor` interface with `asCanonical()` and `asEditable()` methods for translating between editor-specific and canonical formats.
- Custom editors can be registered in `config/rich-text-laravel.php` and selected via the `RICH_TEXT_EDITOR` env variable.
- IMPORTANT: User-generated rich text HTML must always be sanitized before rendering. The package does NOT sanitize automatically.
- IMPORTANT: The plain text output (`toPlainText()`) is not HTML-safe and must be escaped.
- IMPORTANT: When rendering rich text in Blade, use `{!! $post->body !!}` (unescaped) but always sanitize first.
- IMPORTANT: When feeding content back to the editor, always use `toEditorHtml()` instead of the default render output.
