<figure class="attachment attachment--preview" @if ($gallery ?? false) data-trix-attributes='{"presentation":"gallery"}' @endif>
    <a href="{{ $remoteImage->url }}">
        <img src="{{ $remoteImage->url }}" width="{{ $remoteImage->width }}" height="{{ $remoteImage->height }}" />
        @if ($remoteImage->nodeCaption())
            <figcaption class="attachment__caption">
                {{ $remoteImage->nodeCaption() }}
            </figcaption>
        @endif
    </a>
</figure>
