<p align="center" style="margin-top: 2rem; margin-bottom: 2rem;"><img src="/art/rich-text-laravel-logo.svg" alt="Logo Rich Text Laravel" /></p>

<p align="center">
    <a href="https://github.com/tonysm/rich-text-laravel/workflows/run-tests/badge.svg">
        <img src="https://img.shields.io/github/workflow/status/tonysm/rich-text-laravel/run-tests?label=tests" />
    </a>
    <a href="https://packagist.org/packages/tonysm/rich-text-laravel">
        <img src="https://img.shields.io/packagist/dt/tonysm/rich-text-laravel" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/tonysm/rich-text-laravel">
        <img src="https://img.shields.io/github/license/tonysm/rich-text-laravel" alt="License">
    </a>
</p>

Integrates the Trix Editor with Laravel. Inspired by the Action Text gem from Rails.

**🚧 This is still in development. It's not production-ready yet.**

## Installation

You can install the package via composer:

```bash
composer require tonysm/rich-text-laravel
```

Then, you may install it running:

```bash
php artisan richtext:install
```

This will install the package with the recommended model structure. However, you may choose to not want any of the database opinions and only use the custom cast. To achieve that, you may pass the `--no-model` flag:

```bash
php artisan richtext:install --no-model
```

This will only make sure you have the latest version of Trix installed locally and from there, you can use the custom cast on your model. You can find more information about the recommended database structure at the [rich text model](#rich-text-model) section.

## Usage

We're going to extract attachments before saving the rich text field (which uses Trix) in the database and minimize the content for storage. We replace the attachments with `rich-text-attachment` tags. If the attachment for a model attachable, we store a `sgid` which should globally identify that model. When storing images directly (say, for a simple image uploading where you don't have a model for representing that image on your application), we'll fill the `rich-text-attachment` with all the attachment's properties needded to render that image again. Storing a minimized (canonical) version of the rich text content means we don't store the inner contents of the attachment tags, only the metadata needded to render it again when needed.

There are two ways of using the package:

1. With the recommended database structure where all rich text content will be stored outside of the model that has rich text content (recommended); and
1. Only using the `AsRichTextContent` trait to cast a rich text content field on any model, on any table you want.

Below, we cover each usage way.

### The RichText Model
<a name="rich-text-model"></a>

The recommended way is to keep the rich text content outside of the model itself. This will keep the models lean when you're manipulating them, and you can (eagerly or lazily) load the rich text fields only where you need the rich text content.

Here's how you would have two rich text fields on a Post model, say you need one for the body of the content and another one for internal notes you may have:

```php
use Tonysm\RichTextLaravel\Models\Traits\HasRichText;

class Post extends Model
{
    use HasRichText;

    protected $guarded = [];

    protected $richTextFields = [
        'body',
        'notes',
    ];
}
```

This trait will create [dynamic relationships](https://laravel.com/docs/8.x/eloquent-relationships#dynamic-relationships) on the Post model, one for each field. These relationships will be called: `richText{FieldName}` and you may define the fields using underscore, so if you had a `internal_notes` field, that would have a `richTextInternalNotes` relationship added on the model.

For a better DX, the trait will also add a custom cast for the `body` and `notes` fields on the Post model to forward setting/getting operations to the relationship, since these fields will NOT be stored in the posts table. This means that you can use the Post model like this:

```php
$post = Post::create(['body' => $body, 'notes' => $notes]);
```

And you can interact with the rich text fields just like you would with any regular field on the Post model:

```php
$post->body->render();
```

Again, there's no `body` or `notes` fields on the Post model, these _virtual fields_ will forward interactions to the relationship of that field. This means that when you interact with these fields, you're actually interacting with an instance of the `RichText` model. That model will have a `body` field that holds the rich text content. This field is then casted to an instance of the [`Content`](./src/Content.php) class. Calls to the RichText model will be forwarded to the `body` field on the `RichText` model, which is an instance of the `Content` class. This means that instead of:

```php
$post->body->body->attachments();
```

Where the first "body" is the virtual field which will be an instance of the RichText model and the second "body" is the rich text content field on that model, which is an instance of the `Content` class, you can do:

```php
$post->body->attachments();
```

Similarly to the Content class, the RichText model will implement the `__toString` magic method and render the HTML content (for the end user) by casting it to a string, which in blade can be done like this:

```blade
{!! $post->body !!}
```

*Note*: since the HTML output is NOT escaped, make sure you sanitize it before rendering. You can use something like the [mews/purifier](https://github.com/mewebstudio/Purifier) package, see the [sanitization](#sanitization) section for more about this.

The `HasRichText` trait will also add an scope which you can use to eager load the rich text fields (remember, each field will have its own relationship), which you can use like so:

```php
// Loads all rich text fields (1 query for each field, since each has its own relationship)
Post::withRichText()->get();

// Loads only a specific field:
Post::withRichText('body')->get();

// Loads some specific fields (but not all):
Post::withRichText(['body', 'notes'])->get();
```

The database structure for this example would be something like this:

```
posts
    id (primary key)
    created_at (timestamp)
    updated_at (timestamp)

rich_texts
    id (primary key)
    field (string)
    body (long text)
    created_at (timestamp)
    updated_at (timestamp)
```

We store a back-reference to the field name in the `rich_texts` table because a model may have multiple rich text fields, so that is used in the dynamic relationship the `HasRichText` creates for you. There's also a unique constraint on this table, which prevents having multiple entries for the same model/field pair.

Rendering the rich text content back to the Trix editor is a bit differently than rendering for the end users, so you may do that using the `toTrixHtml` method on the field, like so:

```blade
<input id="post_body" value="{!! $post->body->toTrixHtml() !!}" type="hidden" />
<trix-editor input="post_body" class="trix-content"></trix-editor>
```

Next, go to the [attachments](#attachments) section to read more about attachables.

### The AsRichTextContent Trait
<a name="asrichtextcontent-trait"></a>

In case you don't want to use the recommended structure (either because you have strong opinions here or you want to rule your own database structure), you may skip the entire recommended database structure and use the `AsRichTextContent` custom cast on your rich text content field. For instance, if you're storing the `body` field on the `posts` table, you may do it like so:

```php
use Tonysm\RichTextLaravel\Casts\AsRichTextContent;

class Post extends Model
{
    protected $casts = [
        'body' => AsRichTextContent::class,
    ];
}
```

Then the custom cast will parse the HTML content and minify it for storage. Essentially, it will convert this content submitted by Trix which has only an image attachment:

```php
$post->update([
    'content' => <<<HTML
    <h1>Hello World</h1>
    <figure data-trix-attachment='{
        "url": "http://example.com/blue.jpg",
        "width": 300,
        "height": 150,
        "contentType": "image/jpeg",
        "caption": "Something cool",
        "filename":"blue.png",
        "filesize":1168
    }'>
        <img src="http://example.com/blue.jpg" width="300" height="150" />
        <caption>
            Something cool
        </caption>
    </figure>
    HTML,
])
```

To this minified version:

```html
<h1>Hello World</h1>
<rich-text-attachment content-type="image/jpeg" filename="blue.png" filesize="1168" height="300" href="http://example.com/blue.jpg" url="http://example.com/blue.jpg" width="300" caption="testing this caption" presentation="gallery"></rich-text-attachment>
```

And when it renders it again, it will re-render the remote image again inside the `rich-text-attachment` tag. You can render the content for *viewing* by simply echoing out the output, something like this:

```blade
{!! $post->content !!}
```

*Note*: since the HTML output is NOT escaped, make sure you sanitize it before rendering. You can use something like the [mews/purifier](https://github.com/mewebstudio/Purifier) package, see the [sanitization](#sanitization) section for more about this.

When feeding the Trix editor again, you need to do it differently:

```blade
<input id="post_body" value="{!! $post->body->toTrixHtml() !!}" type="hidden" />
<trix-editor input="post_body" class="trix-content"></trix-editor>
```

Rendering for the editor is a bit different, so it has to be like that.

### Trix Attachments
<a name="attachments"></a>

Trix has a concept of Attachments. A common example is attaching images. Trix already ships with an image attachment toolbar button, you only have to implement the actual uploading of the image. Uploaded images may not need a model representation on your application, so you can just have it as a remote image. See the [image upload](#image-upload) section for more about this.

Although images make a straight-forward example, you can actually attach anything to your Trix rich text content. For instance, you may want to implement a mentions feature inside the editor, so you would want to make the User model a Trix attachable. To do that, you may implement the `AttachableContract` and use the `Attachable` trait in a model. Besides that, you may also implement a `richTextRender(array $options): string` where you tell the package how to render that model inside Trix:

```php
use Tonysm\RichTextLaravel\Attachables\AttachableContract;
use Tonysm\RichTextLaravel\Attachables\Attachable;

class User extends Model implements AttachableContract
{
    use Attachable;

    public function richTextRender(array $options = []): string
    {
        return view('users._mention', [
            'user' => $this,
        ])->render();
    }
}
```

Then inside that `users._mention` Blade template you have full control over the HTML for this attachable field.

The `$options` array is there in case you're rendering multiple models inside a gallery, so you would get a `in_gallery` boolean field (optional) in thase case, which is not the case for this user mentions example, so we can simply ignore it.

Trix content can also be converted to Plain Text, so you optionally implement the `richTextAsPlainText()`  method on any attachable and return a plain text string corresponding to that attachment. Read more about this in the [plain text](#plain-text) section.

##### Getting Attachables

You may retrieve all the attachments of a rich content field using the `attachments()` method both in the RichText model instance or the Content instance:

```php
$post->body->attachments()
```

This will return a collection of all the attachments, anything that is an attachable, really, so images and users, for instance - if you want only attachments of a specific attachable you can use the filter method on the collection, like so:

```php
// Getting only attachments of users inside the rich text content.

$post->body->attachments()
    ->filter(fn (Attachment $attachment) => $attachment->attachable instanceof User)
    ->unique();
```

In our user mentions implementation, this may be useful so we can sync the mentions relationship in the post model with all the user attachments in the rich text content, here's a semi-complete implementation of the mentions feature, where we store the mentions associated with the Post and we sync those up everytime the Post model changes to make sure users get the notification even when the Post is updated afterwards to include their mention:

```php
class User extends Model implements AttachableContract
{
    use Attachable;

    public function mentions()
    {
        return $this->hasMany(Mention::class);
    }

    public function richTextRender(array $options = []): string
    {
        return view('users._mention', [
            'user' => $this,
        ])->render();
    }
}

class Mention extends Model
{
    public static function booted()
    {
        static::created(queueable(function (Mention $mention) {
            $mention->sendUserNotification();
        }));
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sendUserNotification(): void
    {
        $this->user->notify(new MentionedNotification($mention));
    }
}

class Post extends Model
{
    use HasRichText;

    protected $guarded = [];

    protected $richTextFields = [
        'body',
    ];

    public static function booted()
    {
        static::saved(queueable(function (Post $post) {
            $post->syncMentions();
        }));
    }

    public function mentions()
    {
        return $this->hasMany(Mention::class);
    }

    public function syncMentions(): void
    {
        $attachedUsers = $this->body->attachments()
            ->filter(fn (Attachment $attachment) => $attachment->attachable instanceof User)
            ->map(fn (Attachment $attachment) => $attachment->attachable)
            ->unique();

        $this->loadMissing('mentions.user');

        // Remove mentions for users that are no longer attached in the content...
        $this->mentions->filter(fn (Mention $mention) => (
            null === $attachedUsers->first(fn (User $user) => $mention->user->is($user)))
        ))->each->delete();

        // Create mentions for users that are now listed and there are no prior mentions for them...
        $newMentions = $attachedUsers->filter(fn (User $user) => (
            null === $this->mentions->first(fn (Mention $mention) => $mention->user->is($user))
        ))->map(fn (User $user) => $user->mentions()->make());

        if ($newMentions->isNotEmpty()) {
            $this->mentions()->saveMany($newMentions);
        }

        $this->mentions->merge($newMentions);
    }
}
```

### Image Upload
<a name="image-upload"></a>

TODO

### Plain Text Rendering
<a name="plain-text"></a>

Trix content can be converted to anything. This essentially means `HTML > something`. The package ships with a `HTML > Plain Text` implementation, so you can convert any Trix content to plain text by calling the `toPlainText()` method on it:

```php
$post->body->toPlainText()
```

As an example, this rich text content:

```html
<h1>Very Important Message<h1>
<p>This is an important message, with the following items:</p>
<ol>
    <li>first item</li>
    <li>second item</li>
</ol>
<p>And here's an image:</p>
<rich-text-attachment content-type="image/jpeg" filename="blue.png" filesize="1168" height="300" href="http://example.com/blue.jpg" url="http://example.com/blue.jpg" width="300" caption="The caption of the image" presentation="gallery"></rich-text-attachment>
<br><br>
<p>With a famous quote</p>
<blockquote>Lorem Ipsum Dolor - Lorense Ipsus</blockquote>
<p>Cheers,</p>
```

Will be converted to:

```plaintext
Very Important Message

This is an important message, with the following items:

    1. first item
    1. second item

And here's an image:

[The caption of the image]

With a famous quote

“Lorem Ipsum Dolor - Lorense Ipsus”

Cheers,
```

If you're attaching models, you can implement the `richTextAsPlainText(?string $caption = null): string` method on it, where you should return the plain text representation of that attachable. If the method is not implemented on the attachable and no caption is stored in the Trix attachment, that attachment won't be present in the Plain Text version of the content.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Tony Messias](https://github.com/tonysm)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
