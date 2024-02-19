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

        <div class="rounded border shadow p-6 trix-content bg-white">
            {{-- DON'T DO THIS. YOU MUST SANITIZE IN     PRODUTION. --}}
            {!! $post->body !!}
        </div>

        <h1 class="text-xl">The Plain Text version:</h1>

        <x-info>
            <span>You may also render the document in plain text. Please, notice that even the plain text version <strong>MUST</strong> be escaped too, as image's captions and other custom attachments may contain user input:</span>
        </x-info>

        <div class="rounded border shadow p-6 space-y-2 whitespace-pre-line bg-white">{{ $post->body->toPlainText() }}</div>

        <h1 class="text-xl">Links in the document:</h1>

        <x-info>
            <span>There's also an easy way to extract links from the document:</span>
        </x-info>

        <ul class="list-disc list-inside">
            @forelse ($post->body->links() as $link)
            <li>{{ $link }}</li>
            @empty
            <li>No links were used.</li>
            @endforelse
        </ul>

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

        <h2 class="text-xl">Comments</h2>

        @foreach($post->comments as $comment)
            <div id="comment_{{ $comment->id }}" class="bg-white p-4 rounded">
                <strong>You said:</strong>
                {{-- YOU MUST ESCAPE THE HTML IN A REAL APP --}}
                <div>{!! $comment->content !!}</div>
            </div>
        @endforeach

        <x-info>Here you can see how you may customize the Trix toolbar for a mimimal look.</x-info>

        <form action="{{ route('posts.comments.store', $post) }}" method="post">
            @csrf

            <div>
                <label class="block font-medium text-sm text-gray-700" for="content">{{ __('New Comment') }}</label>
                <x-trix-input id="create_comment" :accept-files="false" toolbar="minimal" name="content" placeholder="{{ __('Say something with the world...') }}" :value="old('content')" autocomplete="off" />
                <span class="mt-1 block text-sm text-gray-600">{{ __('You may @-mention users here too.') }}</span>
                @error('content')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-4 flex items-center space-x-4 justify-end">
                <x-button type="submit">{{ __('Save') }}</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
