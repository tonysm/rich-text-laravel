<x-app-layout>
    <div class="flex items-center justify-between space-x-2">
        <h1 class="text-4xl font-semibold font-sans">Posts Index</h1>

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
