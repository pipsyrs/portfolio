<?php

namespace App\Actions\Media;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Membersihkan HTML dari editor teks kaya dengan daftar-izin ketat.
 *
 * Konten ini dirender memakai {!! !!} pada landing page (kelas .prose-content),
 * jadi pembersihan dilakukan sekali saat DISIMPAN — bukan saat ditampilkan —
 * supaya tidak ada jalur yang bisa melewatkannya.
 */
class SanitizeRichText
{
    /** Tag yang diizinkan beserta atributnya. */
    private const ALLOWED = [
        'p' => [],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'strike' => [],
        'sub' => [],
        'sup' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'blockquote' => [],
        'h2' => [],
        'h3' => [],
        'code' => [],
        'pre' => [],
        'a' => ['href', 'target', 'rel'],
    ];

    public function __invoke(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        // Hilangkan blok yang isinya sendiri berbahaya sebelum diurai, karena
        // teks di dalamnya tidak boleh ikut terangkat saat tag dibuang.
        $html = preg_replace('#<(script|style|iframe|object|embed|form|svg|math)\b[^>]*>.*?</\1>#is', '', (string) $html) ?? '';
        $html = preg_replace('#<(script|style|iframe|object|embed|form|svg|math)\b[^>]*/?>#i', '', $html) ?? '';

        $document = new DOMDocument;

        $previous = libxml_use_internal_errors(true);

        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="rt-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return null;
        }

        $root = $document->getElementById('rt-root');

        if (! $root instanceof DOMElement) {
            return null;
        }

        $this->clean($root);

        $output = '';

        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $document->saveHTML($child);
        }

        $output = trim($output);

        return $output === '' ? null : $output;
    }

    private function clean(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->nodeName);

                if (! array_key_exists($tag, self::ALLOWED)) {
                    // Tag tidak dikenal dibuang, tapi teks di dalamnya tetap
                    // dipertahankan supaya konten tidak hilang diam-diam.
                    $this->clean($child);
                    $this->unwrap($child);

                    continue;
                }

                $this->stripAttributes($child, self::ALLOWED[$tag]);
                $this->clean($child);

                continue;
            }

            // Komentar bisa menyembunyikan payload pada parser tertentu.
            if ($child->nodeType === XML_COMMENT_NODE) {
                $child->parentNode?->removeChild($child);
            }
        }
    }

    private function stripAttributes(DOMElement $element, array $allowed): void
    {
        foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
            if (! in_array(strtolower($attribute->nodeName), $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }

        if (strtolower($element->nodeName) !== 'a') {
            return;
        }

        $href = trim($element->getAttribute('href'));

        // Hanya skema yang benar-benar aman; javascript:, data:, dan vbscript:
        // semuanya dapat mengeksekusi kode saat tautan diklik.
        $safe = $href !== '' && preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $href) === 1;

        if (! $safe) {
            $element->removeAttribute('href');
            $element->removeAttribute('target');
            $element->removeAttribute('rel');

            return;
        }

        $element->setAttribute('href', $href);

        if ($element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        } else {
            $element->removeAttribute('target');
            $element->removeAttribute('rel');
        }
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
