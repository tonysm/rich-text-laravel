<figure class="not-prose attachment attachment--file attachment--{{ $remoteFile->extension() }}">
    <figcaption class="attachment__caption">
        @if ($remoteFile->caption)
            {{ $remoteFile->caption }}
        @else
            <span class="attachment__name">{{ $remoteFile->filename }}</span>
            <span class="attachment__size">{{ $remoteFile->filesizeForHumans() }}</span>
            <span class="attachment__download"><a class="!underline" href="{{ $remoteFile->url }}">{{ __('Download') }}</a></span>
        @endif
    </figcaption>
</figure>
