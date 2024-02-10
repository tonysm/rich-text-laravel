<x-app-layout>
    <div class="flex items-center justify-start space-x-2">
        <div class="flex items-center justify-end">
            <x-button-link
                href="{{ route('posts.index') }}"
                icon="arrow-uturn-left"
            >{{ __('Index') }}</x-button-link>
        </div>

        <h1 class="text-4xl font-semibold font-sans">{{ __('New Post') }}</h1>
    </div>

    <div class="mt-6 space-y-6">
       <div class="rounded border shadow p-6 space-y-2 bg-white">
            @include('posts.partials.form', ['post' => null])
        </div>
    </div>
</x-app-layout>
