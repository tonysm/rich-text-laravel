<figure class="attachment attachment--preview" @if ($gallery ?? false) data-trix-attributes='{"presentation":"gallery"}' @endif>
    <a href="{{ $remoteImage->url }}">
        <img src="{{ $remoteImage->url }}" width="{{ $remoteImage->width }}" height="{{ $remoteImage->height }}" />
        @if ($remoteImage->caption)
            <figcaption class="attachment__caption">
                {{ $remoteImage->caption }}
            </figcaption>
        @endif
    </a>
</figure>
