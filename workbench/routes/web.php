<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\Message;
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

Route::post('/messages', function () {
    $message = Message::create(request()->validate([
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
    return view('posts.create');
})->name('posts.create');

Route::post('/posts', function () {
    $post = Post::create(request()->validate([
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
    return view('posts.edit', [
        'post' => $post,
    ]);
})->name('posts.edit');

Route::put('/posts/{post}', function (Post $post) {
    $post->update(request()->validate([
        'title' => ['required'],
        'body' => ['required'],
    ]));

    return to_route('posts.show', $post);
})->name('posts.update');

Route::post('/posts/{post}/comments', function (Post $post) {
    $comment = $post->comments()->create(request()->validate([
        'content' => ['required'],
    ]));

    return back()->withFragment(sprintf('#comment_%s', $comment->id));
})->name('posts.comments.store');

Route::get('/livewire', function () {
    return view('livewire.index');
})->name('livewire');

Route::get('/mentions', function (Request $request) {
    return User::query()
        ->when($request->query('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
        ->get()
        ->map(fn (User $user) => [
            'sgid' => $user->richTextSgid(),
            'name' => $user->name,
            'content' => $user->richTextRender(),
        ]);
})->name('mentions.index');

Route::post('attachments', function () {
    request()->validate([
        'attachment' => ['required', 'file'],
    ]);

    $path = request()->file('attachment')->store('trix-attachments', 'public');

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
