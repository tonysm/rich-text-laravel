<div>
    @if ($editingPost ?? false)
    <form wire:submit.prevent="update" class="p-4 rounded shadow bg-white">
        <div>
            <label class="block font-medium text-sm text-gray-700" for="title">{{ __('Title') }}</label>
            <input class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" wire:model.live="form.title" placeholder="{{ __('Title') }}" autofocus autocomplete="off" />
            @error('form.title')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-4">
            <label class="block font-medium text-sm text-gray-700" for="body">{{ __('Body') }}</label>
            <x-trix-input-livewire id="body" name="body" wire:model="form.body" placeholder="{{ __('Share something with the world...') }}" autocomplete="off" />
            <span class="mt-1 block text-sm text-gray-600">{{ __('You may @-mention users.') }}</span>
            @error('form.body')
            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>


        <div class="mt-4 flex items-center space-x-4 justify-end">
            <x-button wire:click="cancel" type="button">{{ __('Cancel') }}</x-button>
            <x-button type="submit">{{ __('Save') }}</x-button>
        </div>
    </form>
    @else
    <ul class="rounded bg-white shadow divide-y">
        @foreach (Workbench\App\Models\Post::all() as $post)
        <li class="p-4 flex items-center justify-between space-x-2">
            <div>
                <div class="text-lg font-medium">{{ $post->title }}</div>
                <div class="text-gray-600 text-sm">{{ Str::limit($post->body->toPlainText(), 80) }}</div>
            </div>

            <button class="text-gray-500 text-sm" type="button" wire:click="edit({{ $post->id }})">Edit</button>
        </li>
        @endforeach
    </ul>
    @endif
</div>
