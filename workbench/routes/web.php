<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Models\Post;

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

Route::redirect('/', '/posts');

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
