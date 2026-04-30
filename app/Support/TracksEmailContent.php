<?php

namespace App\Support;

trait TracksEmailContent
{
    protected function buildTrackedHtml(string $html, int $queueId, bool $includeUnsubscribe = false, ?string $unsubscribeEmail = null): string
    {
        $html = $this->rewriteLinksForTracking($html, $queueId);

        $openUrl = route('track.open', ['id' => $queueId]);
        $pixel = '<img src="' . e($openUrl) . '" alt="" width="1" height="1" style="display:none;" />';

        $injection = $pixel;

        if ($includeUnsubscribe && !empty($unsubscribeEmail)) {
            $unsubscribeUrl = route('unsubscribe', ['email' => rawurlencode($unsubscribeEmail)]);
            $unsubscribeHtml = '<p style="margin-top:16px;font-size:12px;color:#6b7280;">'
                . '<a href="' . e($unsubscribeUrl) . '">Unsubscribe</a>'
                . '</p>';

            $injection = $unsubscribeHtml . $pixel;
        }

        if (stripos($html, '</body>') !== false) {
            return preg_replace('/<\/body>/i', $injection . '</body>', $html, 1) ?? ($html . $injection);
        }

        return $html . $injection;
    }

    protected function rewriteLinksForTracking(string $html, int $queueId): string
    {
        return preg_replace_callback('/<a\b[^>]*\bhref=(["\'])(.*?)\1[^>]*>/i', function ($matches) use ($queueId) {
            $fullTag = $matches[0];
            $href = trim($matches[2]);

            if ($href === '' || str_starts_with($href, '#') || str_starts_with(strtolower($href), 'javascript:')) {
                return $fullTag;
            }

            if (!filter_var($href, FILTER_VALIDATE_URL)) {
                return $fullTag;
            }

            $trackedUrl = route('track.click', [
                'id' => $queueId,
                'url' => $href,
            ]);

            return str_replace($matches[2], e($trackedUrl), $fullTag);
        }, $html) ?? $html;
    }
}
