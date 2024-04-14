<div class="oembed my-2">
    @if (str_contains($attachable->href, 'twitter.com') || str_contains($attachable->href, 'x.com'))
        @include('rich-text-laravel.attachables.partials.twitter-card', ['attachable' => $attachable])
    @else
        @include('rich-text-laravel.attachables.partials.opengraph-card', ['attachable' => $attachable])
    @endif
</div>
