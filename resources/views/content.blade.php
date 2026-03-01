<div class="{{ e(\Tonysm\RichTextLaravel\RichTextLaravel::editorName()) . '-content' }}">
@if (trim($content = $content->renderWithAttachments()))
    {!! $content !!}
@endif
</div>
