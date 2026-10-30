<?php

namespace App\Services;

class RefundPolicyContent
{
    private function isHtml(string $content): bool
    {
        return (bool) preg_match('/<\/?[a-z][^>]*>/i', $content);
    }

    public function forStorage(string $content): string
    {
        if (! $this->isHtml($content)) {
            return $content;
        }
        $clean = app(ShopCommunity::class)->clean($content);
        $text = html_entity_decode(strip_tags($clean), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_replace('/[\s\x{00a0}\x{200b}]+/u', '', $text) === '' ? '' : $clean;
    }

    public function render(?string $content): string
    {
        $content ??= '';

        return $this->isHtml($content) ? app(ShopCommunity::class)->clean($content) : nl2br(e($content), false);
    }

    public function editorHtml(?string $content): string
    {
        $content ??= '';
        if ($this->isHtml($content)) {
            return $this->render($content);
        }

        return collect(preg_split('/\r\n|\r|\n/', $content))->map(fn ($line) => '<p>'.($line === '' ? '<br>' : e($line)).'</p>')->implode('');
    }
}
