<x-app-layout>
    <div class="px-4 sm:px-0">
        <div class="flex items-center justify-between space-x-2">
            <h1 class="text-4xl font-semibold font-sans">Rich Text Laravel Demo</h1>
        </div>

        <div class="mt-6 space-y-4">
            <p>This is a demo application for the Rich Text Laravel package. The goal of this sample application is to show the power of Trix when combined with Laravel.</p>
            <h2 class="text-2xl font-semibold italic">Sample Applications</h2>
            <div class="grid divide-y sm:divide-y-none sm:grid-cols-2 sm:gap-6">
                <div>
                    <a href="{{ route('posts.index') }}" class="flex space-x-2 items-center px-4 py-2 rounded-t-lg sm:rounded-lg bg-gray-300 sm:space-x-0 sm:flex-col sm:space-y-4 sm:justify-center sm:px-4 sm:py-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 sm:w-16 sm:h-16">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        <span class="text-lg font-medium">Visit the Blog application</span>
                    </a>
                </div>
                <div>
                    <a href="{{ route('chat.index') }}" class="flex space-x-2 items-center px-4 py-2 sm:rounded-lg bg-gray-300 sm:space-x-0 sm:flex-col sm:space-y-4 sm:justify-center sm:px-4 sm:py-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 sm:w-16 sm:h-16">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                        </svg>
                        <span class="text-lg font-medium">Visit the Chat application</span>
                    </a>
                </div>
                <div>
                    <a href="{{ route('livewire') }}" class="flex space-x-2 items-center px-4 py-2 rounded-b-lg sm:rounded-lg bg-gray-300 sm:space-x-0 sm:flex-col sm:space-y-4 sm:justify-center sm:px-4 sm:py-6">
                        <svg class="w-6 h-6 sm:w-16 sm:h-16" viewBox="0 0 40 30" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M34.8 27.706C34.12 28.734 33.605 30 32.223 30c-2.326 0-2.452-3.587-4.78-3.587-2.327 0-2.201 3.587-4.527 3.587s-2.452-3.587-4.78-3.587c-2.327 0-2.201 3.587-4.528 3.587-2.326 0-2.452-3.587-4.78-3.587C6.5 26.413 6.628 30 4.3 30c-.731 0-1.245-.354-1.678-.84A19.866 19.866 0 0 1 0 19.24C0 8.613 8.208 0 18.333 0 28.46 0 36.667 8.614 36.667 19.24c0 3.037-.671 5.91-1.866 8.466Z" fill="#FB70A9"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M34.8 27.706C34.12 28.734 33.605 30 32.223 30c-2.326 0-2.452-3.587-4.78-3.587-2.327 0-2.201 3.587-4.527 3.587s-2.452-3.587-4.78-3.587c-2.327 0-2.201 3.587-4.528 3.587-2.326 0-2.452-3.587-4.78-3.587C6.5 26.413 6.628 30 4.3 30c-.731 0-1.245-.354-1.678-.84A19.866 19.866 0 0 1 0 19.24C0 8.613 8.208 0 18.333 0 28.46 0 36.667 8.614 36.667 19.24c0 3.037-.671 5.91-1.866 8.466Z"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M30.834 29.617c4.804-7.147 4.929-15.075.372-23.784a19.19 19.19 0 0 1 5.461 13.447c0 3.026-.695 5.89-1.934 8.434C34.028 28.738 33.493 30 32.06 30c-.49 0-.886-.148-1.226-.383Z" class="fill-gray-700"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.35 24.038c6.376 0 9.06-3.698 9.06-8.95C26.41 9.834 22.355 5 17.35 5c-5.003 0-9.059 4.835-9.059 10.087 0 5.253 2.684 8.951 9.06 8.951Z" class="fill-gray-300"></path>
                            <path d="M14.915 15.385c1.876 0 3.397-1.68 3.397-3.75 0-2.071-1.52-3.75-3.397-3.75-1.876 0-3.397 1.679-3.397 3.75 0 2.07 1.52 3.75 3.397 3.75Z" class="fill-gray-900"></path>
                            <path d="M14.35 12.5c.937 0 1.698-.775 1.698-1.73 0-.957-.76-1.731-1.699-1.731-.938 0-1.699.774-1.699 1.73s.76 1.731 1.7 1.731Z" class="fill-gray-300"></path>
                        </svg>
                        <span class="text-lg font-medium">Livewire Example</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
