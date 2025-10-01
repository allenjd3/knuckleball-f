<?php

namespace App\Actions\OpenGraph;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

class GetLinkMetadata
{
    public function __invoke(string $url, Closure $next)
    {
        $html = Http::get($url)->body();

        $linkSource = new Crawler($html);
        $ogProperties = collect();

        $linkSource->filter('meta[property]')
            ->each(fn ($node) => $ogProperties->put($node->attr('property'), $node->attr('content')));

        $fileName = basename(parse_url($ogProperties->get('og:image'), PHP_URL_PATH));

        $image = '';

        try {
            $imgResponse = Http::get($ogProperties->get('og:image', ''));
            if ($imgResponse->ok()) {
                $image = $imgResponse->body();
            } else {
                return;
            }
        } catch (Throwable $e) {
        }

        if (! is_string($image) || $image === '') {
            $fileName = '';
        } elseif (! Storage::disk('temp')->put($fileName, $image)) {
            $fileName = '';
        }

        return $next([
            'url' => $url,
            'image' => $fileName,
            'title' => $ogProperties->get('og:title'),
            'description' => $ogProperties->get('og:description'),
        ]);
    }
}
