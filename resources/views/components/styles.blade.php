@props(['theme' => 'default'])

@inject('assets', \Tonysm\RichTextLaravel\AssetsManager::class)

@if (config('rich-text-laravel.editor') === 'lexxy')
    <link {{ $attributes }} rel="stylesheet" href="{{ $assets->url('/lexxy.css') }}" />
    <link {{ $attributes }} rel="stylesheet" href="{{ $assets->url('/lexxy-rich-text-laravel-attachments.css') }}" />
@elseif ($theme === 'richtextlaravel')
    <link {{ $attributes }} rel="stylesheet" href="{{ $assets->url('/trix-rich-text-laravel.css') }}" />
    <link {{ $attributes }} rel="stylesheet" href="{{ $assets->url('/trix-rich-text-laravel-attachments.css') }}" />
@else
    <link {{ $attributes }} rel="stylesheet" href="{{ $assets->url('/trix.css') }}" />
    <link {{ $attributes }} rel="stylesheet" href="{{ $assets->url('/trix-rich-text-laravel-attachments.css') }}" />
@endif
