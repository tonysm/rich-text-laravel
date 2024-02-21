<form action="{{ route('messages.store') }}" method="post" class="group p-4 sm:p-0" data-controller="composer">
    @csrf

    <div class="flex items-end space-x-1 px-1 justify-between rounded-3xl border border-gray-400 bg-white focus-within:border-transparent focus-within:ring-4">
        <div class="flex group-focus-within:hidden sm:group-focus-within:flex shrink-0 items-end my-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-9 h-9">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
            </svg>
        </div>

        <div class="group-focus-within:ml-3 sm:group-focus-within:ml-1 flex flex-col my-auto flex-1 space-y-1" style="min-inline-size: 0;">
            @include('chat.partials.trix-input')
        </div>

        <div class="shrink-0 flex flex-row-reverse items-end my-1">
            <button type="submit" class="ml-2 rounded-full bg-black text-white p-2 focus:ring focus:ring-inset focus:ring-white">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                </svg>
                <span class="sr-only">{{ __('Send') }}</span>
            </button>

            <button type="button" data-action="composer#toggleToolbar" class="hidden sm:block rounded-full text-black p-2 focus:ring-2 focus:ring-inset focus:ring-yello-500">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" stroke="currentColor" class="w-5 h-5">
                    <path d="M3 19V1h8a5 5 0 0 1 3.88 8.16A5.5 5.5 0 0 1 11.5 19H3zm7.5-8H7v5h3.5a2.5 2.5 0 1 0 0-5zM7 4v4h3a2 2 0 1 0 0-4H7z"/>
                </svg>

                <span class="sr-only">{{ __('Rich Text') }}</span>
            </button>
        </div>
    </div>

    @error('content')
        <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
    @enderror

    <span class="mt-2 block text-sm text-gray-600">{{ __('You may @-mention users here too.') }}</span>
</form>
