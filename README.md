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

Integrates the [Trix Editor](https://trix-editor.org/) with Laravel. Inspired by the Action Text gem from Rails.

## Installation

You can install the package via composer:

```bash
composer require tonysm/rich-text-laravel
```

Then, you may install it running:

```bash
php artisan richtext:install
```

### Blade Components

If you're using the [Importmap Laravel](https://github.com/tonysm/importmap-laravel) package, make sure you add the Trix core styles Blade Component to the `head` tag on your layout file(s):

```blade
<x-rich-text-trix-styles />
```

If you're using Laravel Mix/Webpack, the `resources/js/trix.js` file has the JS setup and the CSS import as well, so no need to use the the Blade Component.

The package also publishes a Blade Component for you which can be used inside your forms, like so:

```blade
<x-trix-field id="bio" name="bio" />
```

## Overview

We extract attachments before saving the rich text field (which uses Trix) in the database and minimize the content for storage. Attachments are replaced with `rich-text-attachment` tags. Attachments from attachable models have a `sgid` attribute, which should globally identify them in your app.

When storing images directly (say, for a simple image uploading where you don't have a model for representing that attachment in your application), we'll fill the `rich-text-attachment` with all the attachment's properties needded to render that image again. Storing a minimized (canonical) version of the rich text content means we don't store the inner contents of the attachment tags, only the metadata needded to render it again when needed.

There are two ways of using the package:

1. With the recommended database structure where all rich text content will be stored outside of the model that has rich text content (recommended); and
1. Only using the `AsRichTextContent` trait to cast a rich text content field on any model, on any table you want.

Below, we cover each usage way. It's recommended that you at least read the [Trix documentation](https://github.com/basecamp/trix) at some point to get an overview of the client-side of it.

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
    record_type (string)
    record_id (unsigned big int)
    created_at (timestamp)
    updated_at (timestamp)
```

| 💡 If you use UUIDs, you may modify the migration that creates the `rich_texts` table to use `uuidMorphs` instead of `morphs`. However, that means all your model with Rich Text content must also use UUIDs. |
|------------------------|

We store a back-reference to the field name in the `rich_texts` table because a model may have multiple rich text fields, so that is used in the dynamic relationship the `HasRichText` creates for you. There's also a unique constraint on this table, which prevents having multiple entries for the same model/field pair.

Rendering the rich text content back to the Trix editor is a bit differently than rendering for the end users, so you may do that using the `toTrixHtml` method on the field, like so:

```blade
<x-trix-field id="post_body" name="body" value="{!! $post->body->toTrixHtml() !!}" />
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
<x-trix-field id="post_body" name="body" value="{!! $post->body->toTrixHtml() !!}" />
```

Rendering for the editor is a bit different, so it has to be like that.

### Image Upload
<a name="image-upload"></a>

The default image attachment implementation that ships with Trix won't work out of the box with Laravel. It's up to you to implement the image uploading and update the attachment accordingly after that with the image URL. Here's a suggested implementation using Stimulus, but you can do it on any front-end framework of your choice. We won't cover how to setup Stimulus on your project here, check their docs or, if you are already using the [Turbo Laravel](https://github.com/tonysm/turbo-laravel) package, you can see how it [installs Stimulus there](https://github.com/tonysm/turbo-laravel/blob/main/stubs/resources/js/stimulus.js).

First, we need to create the Stimulus controller, let's call it `trix_controller.js`:

```js
import { Controller } from "stimulus";

export default class extends Controller {
    // ...
}
```

Then, we can listen to the `trix-attachment-add` event that the Trix editor dispatched whenever a new attachment is added, like so:

```html
<x-trix-editor
    id="post_body"
    name="body"
    data-controller="trix"
    data-action="
        trix-attachment-add->trix#upload
    "
></x-trix-editor>
```

Now, let's implement the `upload` method in the `trix_controller.js` we just created:

```js
import { Controller } from "stimulus";

export default class extends Controller {
    upload(event) {
        if (! event?.attachment?.file) {
            return;
        }

        this._uploadFile(event.attachment);
    }

    _uploadFile(attachment) {
        const form = new FormData();
        form.append('attachment', attachment.file);

        window.axios.post('/attachments', form, {
            onUploadProgress: (progressEvent) => {
                attachment.setUploadProgress(progressEvent.loaded / progressEvent.total * 100);
            }
        }).then(resp => {
            attachment.setAttributes({
                url: resp.data.image_url,
                href: resp.data.image_url,
            });
        });
    }
}
```

This will send a POST request to `/attachments` with the `attachment` field, which should be a file Blob. The expected response should contain an `image_url` field. Here's what that route in Laravel could look like:

```php
Route::middleware(['auth:sanctum', 'verified'])->post('attachments', function () {
    request()->validate([
        'attachment' => ['required', 'file'],
    ]);

    $path = request()->file('attachment')->store('trix-attachments', 'uploads');

    return [
        'image_url' => Storage::disk('uploads')->url($path),
    ];
})->name('attachments.store');
```

In this example, the image will be stored in the `uploads` disk inside the `trix-attachments/` folder, and the URL to that file will be returned in the `image_url` property. That image will be stored in the Trix content as a remote image. This is only a simplified version of doing image uploads. Another way would be to use something like the [Media Library](https://spatie.be/docs/laravel-medialibrary/v9/introduction) package from Spatie and [customizing the Media model](https://spatie.be/docs/laravel-medialibrary/v9/advanced-usage/using-your-own-model) and make it an attachable too. This way, the Media model would have its own SGID and you would set that attribute in the attachment as well, like so:

```php
attachment.setAttributes({
    sgid: resp.data.sgid,
    url: resp.data.image_url,
    href: resp.data.image_url,
});
```

This would allow more advanced things like retrieving all attachments of the `Media` model in the Rich Text content and saving the embedded Media attachments as a relationship on the model that has rich text content, as we did with mentions in the [attachments](#attachments) section. This way, you have a reference of which images are being used on rich text codes (can be useful if you want to prune the images later).

### Content Attachments
<a name="attachments"></a>

With Trix we can have [content Attachments](https://github.com/basecamp/trix#inserting-a-content-attachment). In order to cover this, let's build a users mentions feature on top of Trix. There's a good [Rails Conf talk](https://youtu.be/2iGBuLQ3S0c?t=1556) building out this entire feature but with Rails. The JavaScript portion is the same, so we're recreating that portion here.

To turn the User model into an _Attachable_, you must implement the `AttachableContract` and use the `Attachable` trait on the User model. Besides that, you must also implement a `richTextRender(array $options): string` where you tell the package how to render that model inside Trix:

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

The `$options` array passed to the `richTextRender` is there in case you're rendering multiple models inside a gallery, so you would get a `in_gallery` boolean field (optional) in that case, which is not the case for this user mentions example, so we can ignore it.

Then inside that `users._mention` Blade template you have full control over the HTML for this attachable field. You may want to show the user's avatar and their name in a span tag inside the attachment, so the `users._mention` view would look like this:

```blade
<span class="flex items-center space-x-1">
    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="inline-block object-cover w-4 h-4 rounded-full" />
    <span>{{ $user->name }}</span>
</span>
```

Now, to the dropdown and to trigger opening the dropdown whenever users type the `@` symbol inside the Trix editor, you may use something like [Zurb's Tribute](https://github.com/zurb/tribute), or you could build your own dropbown and listen to keydown events on the editor watching when users type an `@` symbol to open the dropdown. Your choice. Let's first create a new Stimulus controller for mentions called `mentions_controller.js`:

```js
import { Controller } from "stimulus";

export default class extends Controller {
    // ...
}
```

Next, we're going to import Tribute and initiate it when the controller connects to the DOM element it's attached to - and also detach it when the controller disconnects, as well as which method it will use to look for users (the `fetchUsers`). We need to attach Tribute to the element so it knows where to add the event listeners which trigger the mentions dropdown. We also need to override what Tribute does when an option is picked, that's why we're adding our own implementation of the `range.pasteHtml` method on the instance (see the code below). We also need to :

```js
import { Controller } from "stimulus";
import Tribute from 'tributejs';
import Trix from 'trix';

require('tributejs/tribute.css');

export default class extends Controller {
    connect() {
        this.initializeTribute();
    }

    disconnect() {
        this.tribute.detach(this.element);
    }

    initializeTribute() {
        this.tribute = new Tribute({
            allowSpaces: true,
            lookup: 'name',
            values: this.fetchUsers,
        })

        this.tribute.attach(this.element);
        this.tribute.range.pasteHtml = this._pasteHtml.bind(this);
    }

    fetchUsers(text, callback) {
        window.axios.get(`/mentions?search=${text}`)
            .then(resp => callback(resp.data))
            .catch(error => callback([]))
    }

    _pasteHtml(html, startPosition, endPosition) {
        // We need to remove everything the user has typed
        // while searching for a user. We'll later inject
        // the mention into Trix as a content attachment.

        let range = this.editor.getSelectedRange();
        let position = range[0];
        let length = endPosition - startPosition;

        this.editor.setSelectedRange([position - length, position])
        this.editor.deleteInDirection('backward');
    }
}
```

Now we need to attach the mentions controller to the Trix editor, just like we did with the image upload example:

```html
<x-trix-editor
    id="post_body"
    name="body"
    data-controller="trix mentions"
    data-action="
        trix-attachment-add->trix#upload
    "
></x-trix-editor>
```

The `GET /mentions?search=` route could look something like this:

```php
Route::middleware(['auth:sanctum', 'verified'])->get('mentions', function () {
    return auth()->user()->currentTeam->allUsers()
        ->when(request('search'), fn ($users, $search) => (
            $users->filter(fn (User $user) => (
                str_starts_with(strtolower($user->name), strtolower($search))
            ))
        ))
        ->sortBy('name')
        ->take(10)
        ->map(fn (User $user) => [
            'sgid' => $user->richTextSgid(),
            'name' => $user->name,
            'content' => $user->richTextRender(),
        ])
        ->values();
})->name('mentions.index');
```

You see we're returning the `sgid`, which is a method from the `Attachable` trait. It basically generates a unique global identifier for this model inside your application. More on that in the [SGDI](#sgid) section. It also returns the user's name, which will be used by Tribute to show the options, and the content, which is the rich text render that we're going to insert into the `Trix.Attachment`. However, if you try to run this code yet, this should not work as you'd expect. After choosing an option, the stuff that you wrote while looking for the option, something like `@Ton`, should be gone but no attachment was placed instead. That's because we haven't implemented this part yet.

When you choose an option in Tribute, you need to listen to the `tribute-replaced` event and call a method inside the `mentions_controller.js`, let's hook it:

```html
<x-trix-editor
    id="post_body"
    name="body"
    data-controller="trix mentions"
    data-action="
        trix-attachment-add->trix#upload
        tribute-replaced->mentions#tributeReplaced
    "
></x-trix-editor>
```

Next, let's implement that method inside out mentions controller. The event that we get there should contain the user object (the one with the `sgid`, `name`, and `content` attributes we returned from the Controller) inside the `detail.item.original` path. We can take that and create an instance of the `Trix.Attachment` passing the `sgid` and `content` attributes to it, then inserting that attachment into the editor:

```js
import { Controller } from "stimulus";
import Tribute from 'tributejs';
import Trix from 'trix';

require('tributejs/tribute.css');

export default class extends Controller {
    // ...

    tributeReplaced(e) {
        let mention = e.detail.item.original;
        let attachment = new Trix.Attachment({
            sgid: mention.sgid,
            content: mention.content,
        });

        this.editor.insertAttachment(attachment);
        this.editor.insertString(" ");
    }

    get editor() {
        return this.element.editor;
    }
}
```

Now we're done. The example here is using user mentions, but you could really attach anything into the Trix document. And you have full control over how that document is rendered. When that document is submitted to your backend to be stored, the package will then minimize any content attachments, similar to what was done in the image upload example. But this time, the `sgid` will be used to identify the User attachable that was mentioned and the `users._mention` Blade template will be rendered again later whenever you're displaying that document. This is useful because you can tweak how user mentions look like inside your app without having to worry about the documents at-rest in the database.

You can later retrieve all attachments from that rich text content. See [The Content Object](#content-object) section for more.

### The Content Object
<a name="content-object"></a>

You may want to retrieve all the attachables in that rich text content at a later point and do something fancy with it, say _actually_ storing the User's mentions associated with the Post model, for example. Or you can fetch all the links inside that rich text content and do something with it.

#### Getting Attachments

You may retrieve all the attachments of a rich content field using the `attachments()` method both in the RichText model instance or the Content instance:

```php
$post->body->attachments()
```

This will return a collection of all the attachments, anything that is an attachable, really, so images and users, for instance - if you want only attachments of a specific attachable you can use the filter method on the collection, like so:

```php
// Getting only attachments of users inside the rich text content.
$post->body->attachments()
    ->filter(fn (Attachment $attachment) => $attachment->attachable instanceof User)
    ->map(fn (Attachment $attachment) => $attachment->attachable)
    ->unique();
```

#### Getting Links

To extract links from the rich text content you may call the `links()` method, like so:

```php
$post->body->links()
```

#### Getting Attachment Galleries

Trix has a concept of galleries, you may want to retrieve all the galleries:

```php
$post->body->attachmentGalleries()
```

This should return a collection of all the image gallery `DOMElement`s.

#### Getting Gallery Attachments

You may also want to get only the _attachments_ inside of image galleries. You can achieve that like this:

```php
$post->body->galleryAttachments()
```

Which should return a collection with all the attachments of the images inside galleries (all of them). You can then retrieve just the `RemoteImage` attachable instances like so:

```php
$post->body->galleryAttachments()
    ->map(fn (Attachment $attachment) => $attachment->attachable)
```

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

### Sanitization
<a name="sanitization"></a>

Since we're output unescaped HTML, you need to sanitize to avoid any security issues. One suggestion is to to use the [mews/purifier](https://github.com/mewebstudio/Purifier) package, before any final render (with the exception of rendering inside the value attribute of the input field that feeds Trix). That would look like this:

```php
{!! clean($post->body) !!}
```

You need to add some customizations to the config file created when you install the `mews/purifier` package, like so:

```php
return [
    // ...
    'settings' => [
        // ...
        'default' => [
            // ...
            'HTML.Allowed' => 'rich-text-attachment[sgid|content-type|url|href|filename|filesize|height|width|previewable|presentation|caption|data-trix-attachment|data-trix-attributes],div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src],del,h1,blockquote,figure[data-trix-attributes|data-trix-attachment],figcaption,*[class]',
        ],
        // ...
        'custom_definition' => [
            // ...
            'elements' => [
                // ...
                ['rich-text-attachment', 'Block', 'Flow', 'Common'],
            ],
        ],
        // ...
        'custom_attributes' => [
            // ...
            ['rich-text-attachment', 'sgid', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'content-type', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'url', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'href', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'filename', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'filesize', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'height', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'width', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'previewable', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'presentation', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'caption', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'data-trix-attachment', new HTMLPurifier_AttrDef_Text],
            ['rich-text-attachment', 'data-trix-attributes', new HTMLPurifier_AttrDef_Text],
            ['figure', 'data-trix-attachment', new HTMLPurifier_AttrDef_Text],
            ['figure', 'data-trix-attributes', new HTMLPurifier_AttrDef_Text],
        ],
        // ...
        'custom_elements' => [
            // ...
            ['rich-text-attachment', 'Block', 'Flow', 'Common'],
        ],
    ],
];
```

**Attention**: I'm not an expert in HTML content sanitization, so take this with an extra grain of salt and, please, consult someone more with more security experience on this if you can.

### Email Rendering

If you'd like to send your Trix content by email, it can be rendered in a [Mailable](https://laravel.com/docs/9.x/mail) and delivered to users. Laravel's default email theme presents Trix content cleanly, even if you're using Markdown messages.

To ensure your content displays well across different email clients, you should always sanitize your rendered HTML with the `mews/purifier` package, as detailed above, but using a custom ruleset to remove tags which could affect the message layout.

Add a new `mail` rule to the `mews/purifier` configuration (being mindful of earlier comments about sanitization and security):

```php
return [
    // ...
    'settings' => [
        // ...
        'mail'              => [
            'HTML.Allowed' => 'div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[alt|src],del,h1,h2,sup,blockquote,figure,figcaption,*[class]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true,
        ],
        // ...
```

This rule differs from the normal configuration by removing `width` and `height` tags from `<img>` elements, and turning `<pre>` and `<code>` tags into normal paragraphs (as these seem to trip up the Markdown parser). If you rely on code blocks in Trix, you may be able to adjust the sanitizer ruleset to work around this.

To send the rich text content by email, create a Blade template for the message like in the example below:

```php
@component('mail::message')

# Hi {{ $user->name }},

## We've just published a new article: {{ $article->title }}

<!-- //@formatter:off -->
{!! preg_replace('/^ +/m', '', clean($article->content->render(), 'mail')) !!}
<!-- //@formatter:on -->

@endcomponent
```

Whilst the Blade message uses Markdown for the greeting, the Trix content will be rendered as HTML. This will only render correctly if it's *not indented in any way* — otherwise the Markdown parser tries to interpret nested content as code blocks.

The `preg_replace()` function is used to remove leading whitespace from each rendered line of Trix content, after it's been cleaned. The second parameter in the `clean()` function tells it to reference the `mail` config entry, described above.

The `//@formatter:*` comments are optional, but if you use an IDE like PhpStorm, these comments prevent it from trying to auto-indent the element if you run code cleanup tools.

### SGID

When storing references of custom attachments, the package uses another package called [GlobalID Laravel](https://github.com/tonysm/globalid-laravel). We store a Signed Global ID, which means users cannot simply change the sgids at-rest. They would need to generate another valid signature using the `APP_KEY`, which is secret.

In case you want to rotate your key, you would need to loop-through all the rich text content, take all attachables with an `sgid` attribute, assign a new value to it with the new signature using the new secret, and store the content with that new value.



### Livewire

If you're binding a model that has rich text fields to a Livewire component, you may add the `WithRichTexts` trait to your component. Also, it's recommended that you keep the rich text in raw form until the moment you want to save that to the Rich Text field, something like this:

```php
<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Tonysm\RichTextLaravel\Livewire\WithRichTexts;

class UserProfileForm extends Component
{
    use WithRichTexts;

    public User $user;
    public $bio = '';

    protected $rules = [
        'bio' => ['required'],
    ];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->bio = $user->bio->toTrixHtml();
    }

    public function save()
    {
        $this->validate();

        $this->user->update([
            'bio' => $this->bio,
        ]);
    }
}
```

In this example, the User model has a `bio` rich text field.

<details>
<summary>See the contents of the User model in the example</summary>

```php
<?php

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tonysm\RichTextLaravel\Models\Traits\HasRichText;

class User extends Model
{
    use HasRichText;

    protected $fillable = ['bio'];
    protected $richTextFields = ['bio'];
}
```

</details>

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
