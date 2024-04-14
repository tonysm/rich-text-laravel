<div class="border rounded text-sm shadow p-2 grid grid-cols-6 gap-4">
    <div class="col-span-5 space-y-2">
        <div class="font-semibold"><a href="{{ $attachable->href }}" class="no-underline" rel="noreferrer" target="_blank">{{ $attachable->filename }}</a></div>
        <div class="text-xs">{{ $attachable->description }}</div>
    </div>
    @if ($attachable->url)
    <img class="rounded-full !m-0 !p-0" src="{{ $attachable->url }}" alt="">
    @endif
</div>
