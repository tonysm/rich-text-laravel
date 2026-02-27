@props(['lang' => 'php'])

<details {{ $attributes }}>
    <summary class="text-sm text-gray-500 cursor-pointer hover:text-gray-700">View source</summary>
    <pre class="mt-2 rounded bg-gray-900 text-gray-100 text-sm font-mono p-4 overflow-x-auto"><code class="language-{{ $lang }}">{{ $slot }}</code></pre>
</details>
