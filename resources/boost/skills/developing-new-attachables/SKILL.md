---
name: developing-new-attachables
description: Turn Eloquent models into rich text content attachments (e.g., user mentions, embedded resources) using the AttachableContract and Attachable trait.
---

# Developing New Attachables

## When to use this skill

Use this skill when turning an Eloquent model into a content attachment for rich text editors (e.g., user mentions, embedded resources), or when creating custom non-model attachables (e.g., OpenGraph embeds).

## Making a Model Attachable

### 1. Implement the Contract and Use the Trait

The model must implement `AttachableContract` and use the `Attachable` trait:

@verbatim
```php
use Tonysm\RichTextLaravel\Attachables\AttachableContract;
use Tonysm\RichTextLaravel\Attachables\Attachable;

class User extends Model implements AttachableContract
{
    use Attachable;

    // ...
}
```
@endverbatim

### 2. Implement the `richTextRender()` Method

This is the only required method. It returns the HTML used when displaying the attachment outside the editor:

@verbatim
```php
public function richTextRender(array $options = []): string
{
    return view('mentions.partials.user', [
        'user' => $this,
    ])->render();
}
```
@endverbatim

The `$options` array may contain an `in_gallery` boolean when the attachment is rendered inside a gallery.

### 3. Create the Blade Partial

Create a Blade view that renders the attachment's HTML. This is the content end users will see:

@verbatim
```blade
{{-- resources/views/mentions/partials/user.blade.php --}}
<span class="mention">
    {{ $user->name }}
</span>
```
@endverbatim

### 4. Optional: Plain Text and Markdown Representations

For plain text export (`toPlainText()`), implement:

@verbatim
```php
public function richTextAsPlainText(?string $caption = null): string
{
    return $this->name;
}
```
@endverbatim

For Markdown export (`toMarkdown()`), implement:

@verbatim
```php
public function richTextAsMarkdown(?string $caption = null): string
{
    return $caption ?: $this->name;
}
```
@endverbatim

If these methods are not implemented and no caption is stored, the attachment won't appear in the respective output.

### 5. Optional: Override Default Trait Methods

The `Attachable` trait provides sensible defaults for these methods, but you can override them:

- `richTextContentType(): string` — defaults to `'application/octet-stream'`
- `richTextFilename(): ?string` — defaults to `null`
- `richTextFilesize(): ?int` — defaults to `null`
- `richTextPreviewable(): bool` — defaults to `false`
- `richTextMetadata(?string $key = null): mixed` — defaults to `null`

### 6. Using a Separate Trait for Organization

For better organization, extract the attachable behavior into its own trait:

@verbatim
```php
// app/Models/User/Mentionee.php
namespace App\Models\User;

use Tonysm\RichTextLaravel\Attachables\Attachable;

trait Mentionee
{
    use Attachable;

    public function richTextRender(array $options = []): string
    {
        return view('mentions.partials.user', ['user' => $this])->render();
    }

    public function richTextAsPlainText(?string $caption = null): string
    {
        return e($this->name);
    }

    public function richTextAsMarkdown(?string $caption = null): string
    {
        return $caption ?: e($this->name);
    }
}
```
@endverbatim

Then use it on the model:

@verbatim
```php
class User extends Model implements AttachableContract
{
    use User\Mentionee;
}
```
@endverbatim

## Custom Attachables Without SGIDs

For attachments that don't need a database record (e.g., OpenGraph embeds), implement `AttachableContract` directly on a plain class and register a custom resolver:

@verbatim
```php
use Tonysm\RichTextLaravel\RichTextLaravel;

// In AppServiceProvider::boot()
RichTextLaravel::withCustomAttachables(function (DOMElement $node) {
    if ($attachable = OpengraphEmbed::fromNode($node)) {
        return $attachable;
    }
});
```
@endverbatim

The class must implement `toRichTextAttributes()`, `equalsToAttachable()`, and `richTextRender()`. Use a `content-type` attribute on the node to identify your custom attachment type:

@verbatim
```php
class OpengraphEmbed implements AttachableContract
{
    const CONTENT_TYPE = 'application/vnd.rich-text-laravel.opengraph-embed';

    public static function fromNode(DOMElement $node): ?self
    {
        if ($node->getAttribute('content-type') === static::CONTENT_TYPE) {
            return new self(/* attributes from node */);
        }

        return null;
    }

    public function richTextRender(array $options = []): string
    {
        return view('attachables.opengraph-embed', ['attachable' => $this])->render();
    }

    public function toRichTextAttributes(array $attributes): array
    {
        return [
            'content_type' => static::CONTENT_TYPE,
            'previewable' => true,
            // ... your custom attributes
        ];
    }

    public function equalsToAttachable(AttachableContract $attachable): bool
    {
        return $this->richTextRender() === $attachable->richTextRender();
    }
}
```
@endverbatim

## How SGIDs Work

The `Attachable` trait automatically generates a Signed Global ID (SGID) for the model via `richTextSgid()`. This SGID is stored in the `<rich-text-attachment sgid="...">` tag in the canonical HTML. When content is rendered, the `AttachableFactory` resolves the SGID back to the original model. SGIDs are signed using your `APP_KEY` and never expire for rich text attachments.

## Frontend: Creating Attachments in the Editor

On the frontend, create attachments in the editor when a user selects an attachable. The key data needed from your API endpoint is:

- `sgid` — the model's Signed Global ID
- `content` — the HTML to display inside the editor

For Trix, create a `new Trix.Attachment({ sgid, content, contentType: '...' })` and insert it. For Lexxy or custom editors, the mechanism differs but the concept is the same: embed an attachment node with the `sgid` attribute.

## Retrieving Attachables from Content

After saving, extract all attachables of a specific type:

@verbatim
```php
use Tonysm\RichTextLaravel\Attachment;

$post->body->attachments()
    ->filter(fn (Attachment $attachment) => $attachment->attachable instanceof User)
    ->map(fn (Attachment $attachment) => $attachment->attachable)
    ->unique();
```
@endverbatim
