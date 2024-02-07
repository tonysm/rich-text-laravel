<x-app-layout>
    <div class="flex items-center justify-start space-x-2">
        <div class="flex items-center justify-end">
            <x-button-link
                href="{{ route('posts.show', $post) }}"
                icon="arrow-uturn-left"
            >{{ __('Show') }}</x-button-link>
        </div>

        <h1 class="text-4xl font-semibold font-sans">{{ __('Edit Post') }}</h1>
    </div>

    <div class="mt-6 space-y-6">
       <div class="rounded border shadow p-6 space-y-2">
            @include('posts.partials.form', ['post' => $post])
        </div>
    </div>
</x-app-layout>
