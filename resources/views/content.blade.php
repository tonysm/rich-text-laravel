<div class="trix-content">
@if (trim($trixContent = $content->renderWithAttachments()))
    {!! $trixContent !!}
@endif
</div>
