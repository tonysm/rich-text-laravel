<?php

namespace Workbench\App\Models\Opengraph\OpengraphEmbed;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Tonysm\RichTextLaravel\HtmlConversion;
use Workbench\App\Models\Opengraph\OpengraphEmbed;

trait Fetching
{
    public static function createFromUrl(string $url): ?OpengraphEmbed
    {
        $attributes = static::extractAttributesFromDocument(static::fetchDocument($url));

        return OpengraphEmbed::tryFromAttributes($attributes);
    }

    private static function fetchDocument(string $url)
    {
        return HtmlConversion::document(
            Http::withUserAgent('curl/7.81.0')
                ->maxRedirects(10)
                ->get(static::replaceTwitterDomainFromTweetUrls($url))
                ->throw()
                ->body()
        );
    }

    private static function replaceTwitterDomainFromTweetUrls(string $url)
    {
        $domains = [
            'www.x.com',
            'www.twitter.com',
            'twitter.com',
            'x.com',
        ];

        $host = parse_url($url)['host'];

        return in_array($host, $domains, strict: true)
            ? str_replace($host, 'fxtwitter.com', $url)
            : $url;
    }

    private static function extractAttributesFromDocument(DOMDocument $document): array
    {
        $xpath = new DOMXPath($document);
        $openGraphTags = $xpath->query('//meta[starts-with(@property, "og:") or starts-with(@name, "og:")]');
        $attributes = [];

        foreach ($openGraphTags as $tag) {
            if (! $tag->hasAttribute('content')) {
                continue;
            }

            $key = str_replace('og:', '', $tag->hasAttribute('property') ? $tag->getAttribute('property') : $tag->getAttribute('name'));

            if (! in_array($key, OpengraphEmbed::ATTRIBUTES, true)) {
                continue;
            }

            $attributes[$key] = $tag->getAttribute('content');
        }

        return $attributes;
    }
}
