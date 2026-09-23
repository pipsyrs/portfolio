<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        return response(implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /pipspanel/',
            'Disallow: /lang/',
            'Sitemap: '.route('sitemap'),
            '',
        ]), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $homeUrl = htmlspecialchars(route('index'), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
            .'    <url>'."\n"
            .'        <loc>'.$homeUrl.'</loc>'."\n"
            .'        <changefreq>weekly</changefreq>'."\n"
            .'        <priority>1.0</priority>'."\n"
            .'    </url>'."\n"
            .'</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
