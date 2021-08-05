<div class="attachment-gallery attachment-gallery--{{ count($attachables) }}">
    @foreach ($attachables as $attachable)
        {!! $attachable->richTextRender() !!}
    @endforeach
</div>
