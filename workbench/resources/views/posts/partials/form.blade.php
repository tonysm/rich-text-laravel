<form action="{{ $post?->exists ? route('posts.update', $post) : route('posts.store') }}" method="POST">
    @csrf

    @if($post?->exists ?? false)
        @method('PUT')
    @endif

    <div>
        <label class="block font-medium text-sm text-gray-700" for="title">{{ __('Title') }}</label>
        <input class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="title" placeholder="{{ __('Title') }}" autofocus value="{{ old('title', $post?->title) }}" autocomplete="off" />
        @error('title')
        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </div>

    <div class="mt-4">
        <label class="block font-medium text-sm text-gray-700" for="body">{{ __('Body') }}</label>
        <x-trix-input :id="$post?->exists ? 'post_'.$post->id.'_body' : 'create_post_body'" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="body" placeholder="{{ __('Share something with the world...') }}" :value="old('body', $post?->body?->toTrixHtml())" autocomplete="off" />
        @error('body')
        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </div>

    <div class="mt-4 flex items-center space-x-4 justify-end">
        <x-button type="submit">{{ $post?->exists ? __('Save') : __('Create') }}</x-button>
    </div>
</form>
