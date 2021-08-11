<div class="attachment-gallery attachment-gallery--{{ $attachmentGallery->count() }}">
@foreach ($attachmentGallery->attachments() as $attachment)
    {!! $attachment->richTextRender() !!}
@endforeach
</div>
