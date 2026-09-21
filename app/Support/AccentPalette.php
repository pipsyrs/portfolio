<?php

namespace App\Support;

/**
 * Turunan warna aksen yang lolos kontras WCAG AA.
 *
 * Warna primer diatur pemilik lewat Pengaturan, jadi nilainya bisa apa saja.
 * Dipakai mentah sebagai teks atau latar tombol, warna terang seperti #38bdf8
 * hanya mencapai rasio 1.9:1 terhadap putih. Kelas ini menggeser warna ke arah
 * hitam atau putih sampai memenuhi 4.5:1.
 */
class AccentPalette
{
    private const TARGET = 4.5;

    private const LIGHT_SURFACE = '#ffffff';

    private const DARK_SURFACE = '#0a0e16';

    public function __construct(private readonly string $primary) {}

    /**
     * @return array{primary: string, on_primary: string, text_light: string, text_dark: string}
     */
    public function toArray(): array
    {
        return [
            'primary' => $this->primary,
            'on_primary' => $this->contrast(self::LIGHT_SURFACE, $this->primary) >= self::TARGET
                ? '#ffffff'
                : '#0b1220',
            'text_light' => $this->tune(self::LIGHT_SURFACE, '#000000'),
            'text_dark' => $this->tune(self::DARK_SURFACE, '#ffffff'),
        ];
    }

    /** Geser warna primer sedikit demi sedikit sampai terbaca di atas $background. */
    private function tune(string $background, string $toward): string
    {
        for ($step = 0; $step <= 20; $step++) {
            $candidate = $this->mix($this->primary, $toward, $step * 0.05);

            if ($this->contrast($candidate, $background) >= self::TARGET) {
                return $candidate;
            }
        }

        return $toward;
    }

    private function mix(string $from, string $to, float $amount): string
    {
        [$r1, $g1, $b1] = $this->channels($from);
        [$r2, $g2, $b2] = $this->channels($to);

        return sprintf(
            '#%02x%02x%02x',
            (int) round($r1 + ($r2 - $r1) * $amount),
            (int) round($g1 + ($g2 - $g1) * $amount),
            (int) round($b1 + ($b2 - $b1) * $amount),
        );
    }

    private function contrast(string $a, string $b): float
    {
        $first = $this->luminance($a);
        $second = $this->luminance($b);

        return (max($first, $second) + 0.05) / (min($first, $second) + 0.05);
    }

    private function luminance(string $hex): float
    {
        $linear = array_map(function (int $channel): float {
            $value = $channel / 255;

            return $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }, $this->channels($hex));

        return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
    }

    /** @return array{0: int, 1: int, 2: int} */
    private function channels(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
