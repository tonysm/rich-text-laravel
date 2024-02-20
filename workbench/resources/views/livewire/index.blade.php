<x-app-layout>
    <x-slot name="head">
        @livewireStyles
    </x-slot>

    <div class="flex items-end sm:items-center justify-between space-x-2 px-2 sm:px-0">
        <div class="space-y-4 items-center justify-start sm:space-x-2 sm:space-y-0 sm:flex">
            <x-button-link
                href="{{ route('demo.index') }}"
                icon="arrow-uturn-left"
            >{{ __('Demo') }}</x-button-link>

            <h1 class="text-4xl font-semibold font-sans">{{ __('Livewire Example') }}</h1>
        </div>
    </div>

    <x-info class="mt-6">
        <span>Here we're scanning the rich text document looking for all user attachments (mentions):</span>
    </x-info>

    <div class="mt-6">
        <livewire:posts.index />
    </div>

    @livewireScripts
</x-app-layout>
