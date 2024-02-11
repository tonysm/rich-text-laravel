<x-app-layout>
    <div class="flex items-center justify-start space-x-2">
        <div class="flex items-center justify-end">
            <x-button-link
                href="{{ route('posts.index') }}"
                icon="arrow-uturn-left"
            >{{ __('Index') }}</x-button-link>
        </div>

        <div class="flex items-center space-x-2">
            <h1 class="text-4xl font-semibold font-sans">{{ $post->title }}</h1>

            <a href="{{ route('posts.edit', $post) }}" title="{{ __('Edit Post') }}"><x-icon type="pencil" /></a>
        </div>
    </div>

    <div class="mt-6 space-y-6">
        <h1 class="text-xl">The HTML version:</h1>

        <x-info>
            <span>Here is how the document will render. Notice the content is not escaped. That's dangerous! <strong>YOU MUST</strong> escape the Trix HTML document using something like <a class="text-blue-600 underline underline-offset-4" href="https://symfony.com/doc/current/html_sanitizer.html">Symfony's HTML Sanitizer</a>:</span>
        </x-info>

        <div class="rounded border shadow p-6 space-y-2 trix-content prose prose-pre:text-gray-900 max-w-none bg-white">
            {{-- DON'T DO THIS. YOU MUST SANITIZE IN PRODUTION. --}}
            {!! $post->body !!}
        </div>

        <h1 class="text-xl">The Plain Text version:</h1>

        <x-info>
            <span>You can also render the document in plain text:</span>
        </x-info>

        <div class="rounded border shadow p-6 space-y-2 whitespace-pre-line bg-white">{{ $post->body->toPlainText() }}</div>

        <h2 class="text-xl">User Mentions</h2>

        <x-info>
            <span>Here we're scanning the rich text document looking for all user attachments (mentions):</span>
        </x-info>

        <ul class="list-disc list-inside">
            @forelse ($post->body->attachments()->filter(fn ($attachment) => $attachment->attachable instanceof \Workbench\App\Models\User) as $attachment)
            <li>{{ $attachment->attachable->name }}</li>
            @empty
            <li>No users were mentioned.</li>
            @endforelse
        </ul>
    </div>
</x-app-layout>
