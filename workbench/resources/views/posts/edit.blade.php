<x-app-layout>
    <div class="flex items-end sm:items-center justify-between space-x-2 px-2 sm:px-0">
        <div class="space-y-4 items-center justify-start sm:space-x-2 sm:space-y-0 sm:flex">
            <x-button-link
                href="{{ route('posts.show', $post) }}"
                icon="arrow-uturn-left"
            >{{ __('Show') }}</x-button-link>

            <h1 class="text-4xl font-semibold font-sans">{{ __('Edit Post') }}</h1>
        </div>
    </div>

    <div class="mt-6 space-y-6">
       <div class="rounded border shadow p-6 space-y-2 bg-white">
            @include('posts.partials.form', ['post' => $post])
        </div>
    </div>
</x-app-layout>
