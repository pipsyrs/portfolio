<?php

namespace App\Support;

use App\Models\User;

// Structured data schema.org untuk pemilik portfolio; email, HP, dan alamat lengkap sengaja tidak disertakan.
class PersonSchema
{
    private const SOCIALS = ['linkedin', 'github', 'instagram', 'x_twitter', 'youtube', 'tiktok', 'facebook'];

    public function __construct(private readonly ?User $user) {}

    public function toArray(): array
    {
        if (! $this->user) {
            return [];
        }

        $home = route('index');
        $personId = $home.'#person';
        $websiteId = $home.'#website';

        $person = array_filter([
            '@type' => 'Person',
            '@id' => $personId,
            'name' => $this->user->name,
            'url' => $home,
            'image' => filled($this->user->foto) ? safe_image_url($this->user->foto) : null,
            'jobTitle' => $this->plain($this->user->specialis),
            'description' => $this->plain($this->user->headline),
            'knowsAbout' => $this->knowsAbout(),
            'worksFor' => $this->worksFor(),
            'address' => $this->location(),
            'sameAs' => $this->sameAs(),
        ]);

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    '@id' => $websiteId,
                    'url' => $home,
                    'name' => $this->user->name,
                    'inLanguage' => ['id', 'en'],
                    'publisher' => ['@id' => $personId],
                ],
                array_filter([
                    '@type' => 'ProfilePage',
                    '@id' => $home.'#profile',
                    'url' => $home,
                    'name' => trim($this->user->name.' | '.$this->plain($this->user->specialis), ' |'),
                    'isPartOf' => ['@id' => $websiteId],
                    'mainEntity' => ['@id' => $personId],
                    'dateModified' => $this->user->updated_at?->toIso8601String(),
                ]),
                $person,
            ],
        ];
    }

    private function knowsAbout(): array
    {
        $keywords = settings('seo_keywords') ?: $this->user->keywords;

        if (! is_string($keywords)) {
            return [];
        }

        return collect(explode(',', $keywords))
            ->map(fn (string $keyword) => $this->plain($keyword))
            ->filter()
            ->unique()
            ->take(20)
            ->values()
            ->all();
    }

    private function worksFor(): ?array
    {
        $current = collect(is_array($this->user->careers) ? $this->user->careers : [])
            ->first(fn ($career) => ! empty($career['on_going']) && filled($career['company'] ?? null));

        return $current ? ['@type' => 'Organization', 'name' => $current['company']] : null;
    }

    private function location(): ?array
    {
        $address = array_filter([
            '@type' => 'PostalAddress',
            'addressLocality' => settings('seo_city') ?: null,
            'addressCountry' => settings('seo_country') ?: null,
        ]);

        return count($address) > 1 ? $address : null;
    }

    private function sameAs(): array
    {
        return collect(self::SOCIALS)
            ->map(fn (string $social) => settings($social.'_link'))
            ->filter(fn ($url) => is_string($url)
                && filter_var($url, FILTER_VALIDATE_URL)
                && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true))
            ->values()
            ->all();
    }

    private function plain(?string $value): ?string
    {
        $value = trim(strip_tags((string) $value));

        return $value !== '' ? $value : null;
    }
}
