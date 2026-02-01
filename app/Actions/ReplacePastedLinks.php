<?php

namespace App\Actions;

use DOMDocument;
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

        return $replacePastedLinks->replaceExistingLinks(trim($newHtml, "\n"));
    }

    private function stripExistingLinks(string $html)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $crawler = new Crawler($dom);
        $linkIndex = 0;

        $crawler->filter('a[href]')
            ->each(function (Crawler $link) use (&$linkIndex) {
                $placeholder = "{{LINK_PLACEHOLDER_{$linkIndex}}}";
                $this->extractedLinks[$placeholder] = $link->outerHtml();

                // Replace the actual DOM node
                $node = $link->getNode(0);
                $textNode = $node->ownerDocument->createTextNode($placeholder);
                $node->parentNode->replaceChild($textNode, $node);

                $linkIndex++;
            });

        $modifiedHtml = $dom->saveHTML();

        return $modifiedHtml;
    }

    private function replaceExistingLinks(string $html)
    {
        return str_replace(array_keys($this->extractedLinks), array_values($this->extractedLinks), $html);
    }
}
