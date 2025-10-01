<?php

namespace App\Actions;

use Symfony\Component\DomCrawler\Crawler;

class ReplacePastedLinks
{
    private array $extractedLinks = [];

    public static function handle(string $html)
    {
        $replacePastedLinks = new self;
        $html = $replacePastedLinks->stripExistingLinks($html);

        $links = str($html)->matchAll('/https:\/\/[A-Za-z0-9._~:\/?#\[\]@!$&()*+,;=%-]+/');

        $newHtml = str_replace(
            $links->toArray(),
            $links->map(fn ($link) => "<a href=\"{$link}\">{$link}</a>")->toArray(),
            $html,
        );

        return $replacePastedLinks->replaceExistingLinks($newHtml);
    }

    private function stripExistingLinks(string $html)
    {
        $crawler = new Crawler($html);
        $modifiedHtml = $html;
        $linkIndex = 0;
        $crawler->filter('a[href]')
            ->each(function (Crawler $link) use (&$modifiedHtml, &$linkIndex) {
                $linkHtml = $link->outerHtml();
                $placeholder = "{{LINK_PLACEHOLDER_{$linkIndex}}}";
                $this->extractedLinks[$placeholder] = $linkHtml;

                $modifiedHtml = str_replace($linkHtml, $placeholder, $modifiedHtml);
                $linkIndex++;
            });

        return $modifiedHtml;
    }

    private function replaceExistingLinks(string $html)
    {
        return str_replace(array_keys($this->extractedLinks), array_values($this->extractedLinks), $html);
    }
}
