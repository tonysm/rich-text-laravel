<x-app-layout>
    <div class="flex items-end sm:items-center justify-between space-x-2 px-2 sm:px-0">
        <div class="space-y-4 items-center justify-start sm:space-x-2 sm:space-y-0 sm:flex">
            <x-button-link
                href="{{ route('posts.index') }}"
                icon="arrow-uturn-left"
            >{{ __('Index') }}</x-button-link>

            <h1 class="text-4xl font-semibold font-sans">{{ __('New Post') }}</h1>
        </div>
    </div>

    <div class="mt-6 space-y-6">
       <div class="rounded border shadow p-6 space-y-2 bg-white">
            @include('posts.partials.form', ['post' => null])
        </div>
    </div>
</x-app-layout>
