<div class="flex items-center space-x-2 justify-end">
    @if ($editor === 'lexxy')
    <x-button-link
        href="?editor=trix"
        variant="secondary"
    >{{ __('Switch to Trix') }}</x-button-link>
    @else
    <x-button-link
        href="?editor=lexxy"
        variant="secondary"
    >{{ __('Switch to Lexxy') }}</x-button-link>
    @endif
</div>
