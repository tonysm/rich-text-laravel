<div class="attachment-gallery attachment-gallery--{{ count($attachables) }}">
    @foreach ($attachables as $attachable)
        @include('rich-text-laravel::attachables._remote_image', [
            'remoteImage' => $attachable,
            'gallery' => true
        ])
    @endforeach
</div>
