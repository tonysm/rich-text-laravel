<x-app-layout>
    <div class="flex items-center justify-between space-x-2">
        <h1 class="text-4xl font-semibold font-sans">Rich Text Laravel Demo</h1>
    </div>

    <div class="mt-6 space-y-4">
        <p>This is a demo application for the Rich Text Laravel package. The goal of this sample application is to show the power of Trix when combined with Laravel.</p>

        <h2 class="text-2xl font-semibold italic">Sample Applications</h2>

        <div class="grid grid-cols-2 gap-8">
            <div>
                <a href="{{ route('posts.index') }}" class="flex flex-col space-y-4 items-center justify-center px-4 py-6 rounded-lg bg-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>

                    <span class="text-lg font-medium">Visit the Blog application</span>
                </a>
            </div>

            <div>
                <a href="/chat" class="flex flex-col space-y-4 items-center justify-center px-4 py-6 rounded-lg bg-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>


                    <span class="text-lg font-medium">Visit the Chat application</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
