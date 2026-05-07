<?php

namespace App\Support;

class EmailHtmlPreprocessor
{
    public static function preprocess(string $input): string
    {
        $html = trim($input);

        if ($html === '') {
            return '';
        }

        $html = self::stripUnsafeBlocks($html);
        $html = self::stripInlineEventHandlers($html);
        $html = self::stripUnsupportedPositioningStyles($html);
        $html = self::normalizePlainOrMixedText($html);

        return $html;
    }

    private static function stripUnsafeBlocks(string $html): string
    {
        $patterns = [
            '/<script\b[^>]*>.*?<\/script>/is',
            '/<iframe\b[^>]*>.*?<\/iframe>/is',
            '/<link\b[^>]*>/is',
        ];

        return preg_replace($patterns, '', $html) ?? $html;
    }

    private static function stripInlineEventHandlers(string $html): string
    {
        return preg_replace('/\s+on[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/is', '', $html) ?? $html;
    }

    private static function stripUnsupportedPositioningStyles(string $html): string
    {
        return preg_replace_callback('/style\s*=\s*("|\')(.*?)\1/is', function (array $matches) {
            $quote = $matches[1];
            $styleValue = $matches[2];

            $styleValue = preg_replace('/(?:^|;)\s*(display\s*:\s*(?:flex|grid)\s*;?)/i', ';', $styleValue) ?? $styleValue;
            $styleValue = preg_replace('/(?:^|;)\s*(position\s*:\s*[^;]+;?)/i', ';', $styleValue) ?? $styleValue;
            $styleValue = preg_replace('/;{2,}/', ';', $styleValue) ?? $styleValue;
            $styleValue = trim((string) $styleValue, " ;\t\n\r\0\x0B");

            if ($styleValue === '') {
                return '';
            }

            return 'style=' . $quote . $styleValue . $quote;
        }, $html) ?? $html;
    }

    private static function normalizePlainOrMixedText(string $html): string
    {
        $hasStructuralTags = preg_match('/<(p|br|ul|ol|li|table|tr|td|div|h[1-6]|blockquote)\b/i', $html) === 1;
        $hasAnyTag = preg_match('/<[^>]+>/', $html) === 1;

        if (!$hasAnyTag) {
            $escaped = htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $escaped = self::autoLinkPlainTextUrls($escaped);
            $lines = preg_split("/\r\n|\r|\n/", $escaped) ?: [];

            $paragraphs = [];
            $buffer = [];

            foreach ($lines as $line) {
                $trimmed = trim($line);

                if ($trimmed === '') {
                    if (!empty($buffer)) {
                        $paragraphs[] = '<p>' . implode('<br>', $buffer) . '</p>';
                        $buffer = [];
                    }
                    continue;
                }

                $buffer[] = $trimmed;
            }

            if (!empty($buffer)) {
                $paragraphs[] = '<p>' . implode('<br>', $buffer) . '</p>';
            }

            return implode('', $paragraphs);
        }

        if (!$hasStructuralTags) {
            $html = self::autoLinkPlainTextUrls($html);
            $html = preg_replace("/\r\n|\r|\n/", "<br>\n", $html) ?? $html;
            return '<p>' . $html . '</p>';
        }

        return $html;
    }

    private static function autoLinkPlainTextUrls(string $text): string
    {
        return preg_replace_callback(
            '/(?<!["\'>])\b((https?:\/\/|www\.)[^\s<]+)/i',
            function (array $matches): string {
                $raw = $matches[1];

                $trimmed = rtrim($raw, '.,;:!?)');
                $trailing = substr($raw, strlen($trimmed));

                $href = str_starts_with(strtolower($trimmed), 'www.')
                    ? 'https://' . $trimmed
                    : $trimmed;

                return '<a href="' . $href . '">' . $trimmed . '</a>' . $trailing;
            },
            $text
        ) ?? $text;
    }
}
