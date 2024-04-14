<div class="border rounded text-sm shadow p-2 grid gap-4 divide-y">
    @if ($attachable->url)
    <img class="!m-0 !p-0" src="{{ $attachable->url }}" alt="">
    @endif
    <div class="pt-1 space-y-2">
        <div class="font-semibold"><a href="{{ $attachable->href }}" class="no-underline" rel="noreferrer" target="_blank">{{ $attachable->filename }}</a></div>
        <div class="text-xs">{{ $attachable->description }}</div>
    </div>
</div>
