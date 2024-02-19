<div class="message grid grid-cols-3 my-2">
    <div
        @class([
            "message__content max-w-none rounded bg-white p-3 col-span-2 shadow-sm",
            "col-start-2" => $message->created_at->gte(now()->subSeconds(10)),
        ])
    >
        <div class="prose prose-pre:text-gray-900 prose-blockquote:my-0">
            {!! $message->content !!}
        </div>

        <div class="flex justify-end">
            <time class="text-gray-400 text-xs" datetime="{{ $message->created_at->toIsoString() }}">{{ $message->created_at->diffForHumans() }}</time>
        </div>
    </div>
</div>
