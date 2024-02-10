<div class="relative rounded border shadow p-6 bg-white">
    <p class="text-base"><a href="{{ route('posts.show', $post) }}">{{ $post->title }} <span class="absolute inset-0"></span></a></p>
    <p class="text-sm text-gray-700">{{ Str::limit($post->body->toPlainText(), 80) }}</p>
</div>
