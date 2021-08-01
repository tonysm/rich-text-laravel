<figure class="attachment attachment--preview">
    <img src="{{ $remoteImage->url }}" width="{{ $remoteImage->width }}" height="{{ $remoteImage->height }}" />
    @if ($remoteImage->caption)
        <figcaption class="attachment__caption">
            {{ $remoteImage->caption }}
        </figcaption>
    @endif
</figure>
