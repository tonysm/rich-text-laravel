<x-app-layout margin="">
   <div class="h-screen flex flex-col space-y-4 justify-between sm:p-10 overflow-hidden">
       <div class="flex items-center justify-between space-x-2">
            <div class="flex items-center justify-start space-x-2 px-4 pt-4 sm:px-0 sm:py-0">
                <x-button-link
                    href="{{ route('demo.index') }}"
                    icon="arrow-uturn-left"
                >{{ __('Demo') }}</x-button-link>
                <h1 class="text-4xl font-semibold font-sans">Chat Index</h1>
            </div>
        </div>

        <div class="messages overflow-y-auto flex-1 flex flex-col flex-col-reverse p-4 sm:rounded-lg border-2 border-gray-300 border-dashed">
            @each('chat.partials.message', $messages, 'message')
        </div>

        @include('chat.partials.composer')
   </div>
</x-app-layout>
