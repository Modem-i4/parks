<?php

namespace App\Http\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class SanitizeMarkerDescriptionService
{
    private const ALLOWED_TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a'];
    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed'];

    public function sanitize(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $html = $this->normalizePlainText($html);

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div id="marker-description-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('marker-description-root');
        if (!$root) {
            return null;
        }

        $this->cleanChildren($root);

        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim($clean) === '' ? null : trim($clean);
    }

    private function normalizePlainText(string $value): string
    {
        if (preg_match('/<\/?[a-z][^>]*>/i', $value)) {
            return $value;
        }

        $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $paragraphs = preg_split('/(?:\r?\n){2,}/', $escaped) ?: [$escaped];

        return implode('', array_map(
            fn (string $paragraph) => '<p>'.preg_replace('/\r?\n/', '<br>', $paragraph).'</p>',
            $paragraphs,
        ));
    }

    private function cleanChildren(DOMNode $parent): void
    {
        for ($node = $parent->firstChild; $node !== null;) {
            $next = $node->nextSibling;

            if ($node->nodeType === XML_COMMENT_NODE) {
                $parent->removeChild($node);
            } elseif ($node instanceof DOMElement) {
                $tag = strtolower($node->tagName);

                if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                    $parent->removeChild($node);
                } elseif (!in_array($tag, self::ALLOWED_TAGS, true)) {
                    $this->cleanChildren($node);
                    while ($node->firstChild) {
                        $parent->insertBefore($node->firstChild, $node);
                    }
                    $parent->removeChild($node);
                } else {
                    $this->cleanElement($node, $tag);
                    $this->cleanChildren($node);
                }
            }

            $node = $next;
        }
    }

    private function cleanElement(DOMElement $element, string $tag): void
    {
        $href = $tag === 'a' ? trim($element->getAttribute('href')) : '';

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $element->removeAttribute($attribute->name);
        }

        if ($tag !== 'a') {
            return;
        }

        if (!$this->isSafeUrl($href)) {
            return;
        }

        $element->setAttribute('href', $href);
        $element->setAttribute('target', '_blank');
        $element->setAttribute('rel', 'noopener noreferrer nofollow');
    }

    private function isSafeUrl(string $url): bool
    {
        if ($url === '' || preg_match('/[\x00-\x20]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return !str_starts_with($url, '//');
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), ['http', 'https', 'mailto'], true);
    }
}
