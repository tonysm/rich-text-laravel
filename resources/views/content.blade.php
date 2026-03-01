<div @class([
    'trix-content' => \Tonysm\RichTextLaravel\RichTextLaravel::editorName() === 'trix',
    'lexxy-content' => \Tonysm\RichTextLaravel\RichTextLaravel::editorName() === 'lexxy',
    \Tonysm\RichTextLaravel\RichTextLaravel::editorName() . '-content' => ! in_array(\Tonysm\RichTextLaravel\RichTextLaravel::editorName(), ['trix', 'lexxy']),
])>
@if (trim($content = $content->renderWithAttachments()))
    {!! $content !!}
@endif
</div>
