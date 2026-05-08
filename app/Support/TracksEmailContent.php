<?php

namespace App\Support;

trait TracksEmailContent
{
    protected function buildTrackedHtml(
        string $html,
        int $queueId,
        bool $includeUnsubscribe = false,
        ?string $unsubscribeEmail = null,
        array $utm = []
    ): string {
        $html = $this->rewriteLinksForTracking($html, $queueId, $utm);

        $openUrl = route('track.open', ['id' => $queueId]);
        $pixel = '<img src="' . e($openUrl) . '" alt="" width="1" height="1" style="display:none;" />';

        $injection = $pixel;

        if ($includeUnsubscribe && !empty($unsubscribeEmail)) {
            $unsubscribeUrl = route('unsubscribe', ['email' => rawurlencode($unsubscribeEmail)]);
            $appName = config('app.name', 'Novelio Technologies');

            $unsubscribeHtml = '
<div style="margin-top:32px;padding-top:24px;border-top:1px solid #e5e7eb;text-align:center;font-family:Arial,Helvetica,sans-serif;">
    <p style="margin:0 0 6px 0;font-size:12px;color:#9ca3af;line-height:1.5;">
        You are receiving this email because you subscribed to communications from
        <strong style="color:#6b7280;">' . e($appName) . '</strong>.
    </p>
    <p style="margin:0 0 10px 0;font-size:12px;color:#9ca3af;line-height:1.5;">
        If you no longer wish to receive these emails, you can
        <a href="' . e($unsubscribeUrl) . '"
           style="color:#6366f1;text-decoration:underline;font-weight:600;">unsubscribe here</a>.
    </p>
    <p style="margin:0;font-size:11px;color:#d1d5db;">
        &copy; ' . date('Y') . ' ' . e($appName) . '. All rights reserved.
    </p>
</div>';

            $injection = $unsubscribeHtml . $pixel;
        }

        if (stripos($html, '</body>') !== false) {
            return preg_replace('/<\/body>/i', $injection . '</body>', $html, 1) ?? ($html . $injection);
        }

        return $html . $injection;
    }

    protected function rewriteLinksForTracking(string $html, int $queueId, array $utm = []): string
    {
        return preg_replace_callback('/<a\b[^>]*\bhref=(["\'])(.*?)\1[^>]*>/i', function ($matches) use ($queueId, $utm) {
            $fullTag = $matches[0];
            $href = trim($matches[2]);
            $lowerHref = strtolower($href);

            if (
                $href === '' ||
                str_starts_with($href, '#') ||
                str_starts_with($lowerHref, 'javascript:') ||
                str_starts_with($lowerHref, 'mailto:') ||
                str_starts_with($lowerHref, 'tel:')
            ) {
                return $fullTag;
            }

            if (!filter_var($href, FILTER_VALIDATE_URL)) {
                return $fullTag;
            }

            if (str_contains($lowerHref, '/unsubscribe/')) {
                return $fullTag;
            }

            $finalUrl = $this->appendUtmParams($href, $utm);

            $trackedUrl = route('track.click', [
                'id' => $queueId,
                'url' => $finalUrl,
            ]);

            $encodedTrackedUrl = htmlspecialchars($trackedUrl, ENT_QUOTES, 'UTF-8', false);

            return preg_replace(
                '/(\bhref=(["\']))(.*?)(\2)/i',
                '$1' . $encodedTrackedUrl . '$4',
                $fullTag,
                1
            ) ?? $fullTag;
        }, $html) ?? $html;
    }

    protected function appendUtmParams(string $url, array $utm = []): string
    {
        $utm = array_filter([
            'utm_source' => trim((string) ($utm['utm_source'] ?? '')),
            'utm_medium' => trim((string) ($utm['utm_medium'] ?? '')),
            'utm_campaign' => trim((string) ($utm['utm_campaign'] ?? '')),
            'utm_term' => trim((string) ($utm['utm_term'] ?? '')),
            'utm_content' => trim((string) ($utm['utm_content'] ?? '')),
        ], static fn ($value) => $value !== '');

        if (empty($utm)) {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $existingQuery = [];
        parse_str($parts['query'] ?? '', $existingQuery);

        $mergedQuery = array_merge($existingQuery, $utm);
        $queryString = http_build_query($mergedQuery);

        $rebuilt = '';

        if (!empty($parts['scheme'])) {
            $rebuilt .= $parts['scheme'].'://';
        }

        if (!empty($parts['user'])) {
            $rebuilt .= $parts['user'];
            if (!empty($parts['pass'])) {
                $rebuilt .= ':'.$parts['pass'];
            }
            $rebuilt .= '@';
        }

        if (!empty($parts['host'])) {
            $rebuilt .= $parts['host'];
        }

        if (!empty($parts['port'])) {
            $rebuilt .= ':'.$parts['port'];
        }

        $rebuilt .= $parts['path'] ?? '';
        if ($queryString !== '') {
            $rebuilt .= '?'.$queryString;
        }

        if (!empty($parts['fragment'])) {
            $rebuilt .= '#'.$parts['fragment'];
        }

        return $rebuilt !== '' ? $rebuilt : $url;
    }
}
