<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\Message;
use Workbench\App\Models\Opengraph\OpengraphEmbed;
use Workbench\App\Models\Post;
use Workbench\App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/demo');

Route::get('/demo', function () {
    return view('demo');
})->name('demo.index');

Route::get('/chat', function () {
    return view('chat.index', [
        'messages' => Message::query()
            ->withRichText('content')
            ->ordered()
            ->get(),
    ]);
})->name('chat.index');

Route::post('/messages', function (Request $request) {
    $message = Message::create($request->validate([
        'content' => ['required'],
    ]));

    return redirect()->back()->withFragment("#message_{$message->id}");
})->name('messages.store');

Route::get('/posts', function () {
    return view('posts.index', [
        'posts' => Post::query()
            ->latest()
            ->get(),
    ]);
})->name('posts.index');

Route::get('/posts/create', function () {
    $editor = match (request('editor', '')) {
        'lexxy' => 'lexxy',
        default => 'trix',
    };

    config()->set('rich-text-laravel.editor', $editor);

    return view('posts.create', [
        'editor' => $editor,
    ]);
})->name('posts.create');

Route::post('/posts', function (Request $request) {
    $editor = match (request('editor', '')) {
        'lexxy' => 'lexxy',
        default => 'trix',
    };

    config()->set('rich-text-laravel.editor', $editor);

    $post = Post::create($request->validate([
        'title' => ['required'],
        'body' => ['required'],
    ]));

    return to_route('posts.show', $post);
})->name('posts.store');

Route::get('/posts/{post}', function (Post $post) {
    return view('posts.show', [
        'post' => $post,
    ]);
})->name('posts.show');

Route::get('/posts/{post}/edit', function (Post $post) {
    $editor = match (request('editor', '')) {
        'lexxy' => 'lexxy',
        default => 'trix',
    };

    config()->set('rich-text-laravel.editor', $editor);

    return view('posts.edit', [
        'post' => $post,
        'editor' => $editor,
    ]);
})->name('posts.edit');

Route::put('/posts/{post}', function (Request $request, Post $post) {
    $editor = match (request('editor', '')) {
        'lexxy' => 'lexxy',
        default => 'trix',
    };

    config()->set('rich-text-laravel.editor', $editor);

    $post->update($request->validate([
        'title' => ['required'],
        'body' => ['required'],
    ]));

    return to_route('posts.show', $post);
})->name('posts.update');

Route::post('/posts/{post}/comments', function (Request $request, Post $post) {
    $comment = $post->comments()->create($request->validate([
        'content' => ['required'],
    ]));

    return back()->withFragment(sprintf('#comment_%s', $comment->id));
})->name('posts.comments.store');

Route::get('/livewire', function () {
    return view('livewire.index');
})->name('livewire');

Route::get('/mentions', function (Request $request) {
    $users = User::query()
        ->when($request->query('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
        ->get();

    if ($request->wantsJson()) {
        return $users->map(fn (User $user) => [
            'sgid' => $user->richTextSgid(),
            'name' => $user->name,
            'content' => $user->richTextRender(),
        ]);
    }

    return view('mentions.index', [
        'users' => $users,
    ]);
})->name('mentions.index');

Route::post('attachments', function (Request $request) {
    $request->validate([
        'attachment' => ['required', 'file'],
    ]);

    $path = $request->file('attachment')->store('trix-attachments', 'public');

    return [
        'image_url' => route('attachments.show', $path),
    ];
})->name('attachments.store');

// This route wouldn't exist in a real app
Route::get('/attachments/{path}', function (string $path) {
    $disk = Storage::disk('public');

    abort_unless($disk->exists($path), 404);

    $stream = $disk->readStream($path);

    $headers = [
        'Content-Type' => $disk->mimeType($path),
        'Content-Length' => $disk->size($path),
    ];

    return response()->stream(fn () => fpassthru($stream), 200, $headers);
})->name('attachments.show')->where('path', '.*');

Route::post('/opengraph-embeds', function (Request $request) {
    $request->validate([
        'url' => [
            'bail',
            'required',
            'url',
            function (string $attribute, mixed $value, Closure $fail): void {
                $ip = gethostbyname($host = parse_url($value)['host']);

                // Prevent sniffing domains resolved to private IP ranges...
                if ($ip === $host || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                    $fail(__('URL is invalid.'));
                }
            },
        ],
    ]);

    if ($opengraph = OpengraphEmbed::createFromUrl($request->url)) {
        return $opengraph->toArray();
    }

    return response()->noContent();
});
