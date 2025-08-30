<?php

namespace App\Actions;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class GetLinkMetadata
{
    public function __invoke(string $url, Closure $next)
    {
        $html = Http::get($url)->body();

        $linkSource = new Crawler($html);
        $ogProperties = collect();

        $linkSource->filter('meta[property]')
            ->each(fn ($node) => $ogProperties->put($node->attr("property"), $node->attr("content")));

        $fileName = array_slice(explode('/', $ogProperties->get('og:image')), -1)[0];
        $image = Http::get($ogProperties->get('og:image'))->body();

        Storage::disk('temp')->put($fileName, $image);
        return $next([
            'url' => $url,
            'image' => $fileName,
            'title' => $ogProperties->get('og:title'),
            'description' => $ogProperties->get('og:description'),
        ]);
    }
}