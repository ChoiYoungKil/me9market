<?php

namespace App\Services;

class AdminNoticeContent
{
    public function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }
        $doc = new \DOMDocument;
        $before = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($before);
        $allowed = ['div', 'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'span', 'ul', 'ol', 'li', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'a', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'img', 'hr', 'pre', 'code'];
        foreach (iterator_to_array($doc->getElementsByTagName('*')) as $node) {
            if (! $node->parentNode) {
                continue;
            }$tag = strtolower($node->nodeName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'link', 'meta', 'form', 'input', 'button', 'textarea', 'select'], true)) {
                $node->parentNode->removeChild($node);

                continue;
            }
            if (! in_array($tag, $allowed, true)) {
                while ($node->firstChild) {
                    $node->parentNode->insertBefore($node->firstChild, $node);
                }$node->parentNode->removeChild($node);

                continue;
            }
            foreach (iterator_to_array($node->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $value = trim($attribute->value);
                $keep = false;
                if ($tag === 'a' && $name === 'href') {
                    $keep = (bool) preg_match('~^(https?://[^\s]+|mailto:[^\s]+|/(?!/)[^\s]*)$~iu', $value);
                }
                if ($tag === 'img' && $name === 'src') {
                    $path = parse_url($value, PHP_URL_PATH);
                    $host = parse_url($value, PHP_URL_HOST);
                    $scheme = parse_url($value, PHP_URL_SCHEME);
                    $keep = ($scheme === null || in_array(strtolower((string) $scheme), ['http', 'https'], true)) && ! str_starts_with($value, '//') && (! $host || strcasecmp($host, (string) parse_url(config('app.url'), PHP_URL_HOST)) === 0 || strcasecmp($host, request()->getHost()) === 0) && is_string($path) && preg_match('~^/admin/attachments/notices/[a-f0-9-]{36}\.(png|jpe?g|webp)$~i', $path);
                }
                if ($tag === 'img' && $name === 'alt') {
                    $node->setAttribute('alt', mb_substr($value, 0, 200));
                    $keep = true;
                }
                if (in_array($name, ['colspan', 'rowspan'], true) && in_array($tag, ['td', 'th'], true)) {
                    $keep = ctype_digit($value) && (int) $value > 0 && (int) $value <= 20;
                }
                if ($name === 'style') {
                    $style = $this->styles($value);
                    $keep = $style !== '';
                    if ($keep) {
                        $node->setAttribute('style', $style);
                    }
                }
                if (! $keep) {
                    $node->removeAttribute($name);
                }
            }
            if ($tag === 'img' && ! $node->hasAttribute('src')) {
                $node->parentNode?->removeChild($node);
            }
        }
        $out = '';
        foreach ($doc->documentElement?->childNodes ?? [] as $node) {
            $out .= $doc->saveHTML($node);
        }

return $out;
    }

    private function styles(string $input): string
    {
        $safe = [];
        foreach (explode(';', $input) as $part) {
            $pair = explode(':', $part, 2);
            if (count($pair) !== 2) {
                continue;
            }[$key,$value] = array_map('trim', $pair);
            $key = strtolower($key);
            $okay = false;
            if (in_array($key, ['color', 'background-color'], true)) {
                $okay = (bool) preg_match('/^(#[a-f0-9]{3,8}|[a-z]{3,20}|rgba?\([0-9.,%\s]+\))$/i', $value);
            }
            if ($key === 'font-size') {
                $okay = (bool) preg_match('/^[0-9]{1,3}(\.[0-9]+)?(pt|px|em|rem|%)$/i', $value);
            }
            if ($key === 'font-family') {
                $okay = (bool) preg_match('/^[a-zA-Z0-9\s,\-\'"가-힣]{1,150}$/u', $value);
            }
            if ($key === 'text-align') {
                $okay = in_array($value, ['left', 'center', 'right', 'justify'], true);
            }
            if ($key === 'font-weight') {
                $okay = in_array($value, ['normal', 'bold', '400', '500', '600', '700', '800'], true);
            }
            if ($key === 'text-decoration') {
                $okay = in_array($value, ['underline', 'line-through', 'none'], true);
            }
            if ($key === 'width') {
                $okay = (bool) preg_match('/^[0-9]{1,4}(px|%)$/', $value);
            }
            if ($key === 'border-collapse') {
                $okay = in_array($value, ['collapse', 'separate'], true);
            }
            if ($okay) {
                $safe[] = $key.':'.$value;
            }
        }

        return implode(';',$safe);
    }
}
