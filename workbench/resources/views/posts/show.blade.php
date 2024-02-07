<x-app-layout>
    <div class="flex items-center justify-start space-x-2">
        <div class="flex items-center justify-end">
            <x-button-link
                href="{{ route('posts.index') }}"
                icon="arrow-uturn-left"
            >{{ __('back') }}</x-button-link>
        </div>

        <h1 class="text-4xl font-semibold font-sans">{{ $post->title }}</h1>
    </div>

    <div class="mt-6 space-y-6">
        <h1 class="text-xl">The HTML version:</h1>
        <div class="rounded border shadow p-6 space-y-2 trix-content prose prose-pre:text-gray-900 max-w-none">
            {{-- DON'T DO THIS. YOU MUST SANITIZE IN PRODUTION. --}}
            {!! $post->body !!}
        </div>

        <h1 class="text-xl">The Plain Text version:</h1>

        <div class="rounded border shadow p-6 space-y-2 whitespace-pre-line">
            {{ $post->body->toPlainText() }}
        </div>
    </div>
</x-app-layout>
