<x-app-layout>
    <div class="flex items-end sm:items-center justify-between space-x-2 px-2 sm:px-0">
        <div class="space-y-4 items-center justify-start sm:space-x-2 sm:space-y-0 sm:flex">
            <x-button-link
                href="{{ route('demo.index') }}"
                icon="arrow-uturn-left"
            >{{ __('Demo') }}</x-button-link>

            <h1 class="text-4xl font-semibold font-sans">Posts Index</h1>
        </div>

        <div class="flex items-center justify-end">
            <x-button-link
                href="{{ route('posts.create') }}"
                icon="plus"
            >{{ __('Write') }}</x-button-link>
        </div>
    </div>

    <div class="mt-6 space-y-4">
        @each('posts.partials.post_card', $posts, 'post')

        @if($posts->isEmpty())
            @include('posts.partials.empty_state')
        @endif
    </div>
</x-app-layout>
