<?php

namespace App\Http\Controllers;

use App\Models\Umkm;
use App\Models\Desa;
use App\Models\Berita;
use App\Models\WorkDirectory;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate XML Sitemap
     */
    public function index()
    {
        $sitemap = \Illuminate\Support\Facades\Cache::remember('sitemap_xml_data', now()->addHours(24), function () {
            $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
            $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            // Homepage
            $sitemap .= $this->addUrl(url('/'), '1.0', 'daily');

            // Static Pages
            $sitemap .= $this->addUrl(route('public.tracking'), '0.8', 'weekly');

            // Desa Pages
            $desas = Desa::all();
            foreach ($desas as $desa) {
                $url = url('/desa/' . $desa->id); // Adjust based on your route
                $sitemap .= $this->addUrl($url, '0.7', 'weekly', $desa->updated_at);
            }

            // UMKM Pages — filter hanya UMKM aktif (status = 'aktif')
            $umkms = Umkm::where('status', Umkm::STATUS_AKTIF)->get();
            foreach ($umkms as $umkm) {
                $url = route('umkm_rakyat.show', $umkm->slug ?? $umkm->id);
                $sitemap .= $this->addUrl($url, '0.6', 'weekly', $umkm->updated_at);
            }

            // Berita Pages
            $beritas = Berita::published()->get();
            foreach ($beritas as $berita) {
                $url = route('public.berita.show', $berita->slug);
                $sitemap .= $this->addUrl($url, '0.5', 'monthly', $berita->updated_at);
            }

            // Work Directory Pages (Jasa/Pekerjaan)
            $sitemap .= $this->addUrl(route('economy.index', ['tab' => 'jasa']), '0.8', 'weekly');
            $workItems = WorkDirectory::public()->get();
            foreach ($workItems as $work) {
                $url = route('economy.show', $work->id);
                $sitemap .= $this->addUrl($url, '0.6', 'weekly', $work->updated_at);
            }

            $sitemap .= '</urlset>';
            
            return $sitemap;
        });

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Helper to add URL to sitemap
     */
    private function addUrl($loc, $priority = '0.5', $changefreq = 'weekly', $lastmod = null)
    {
        $url = '<url>';
        $url .= '<loc>' . htmlspecialchars($loc) . '</loc>';

        if ($lastmod) {
            $url .= '<lastmod>' . $lastmod->format('Y-m-d') . '</lastmod>';
        } else {
            $url .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
        }

        $url .= '<changefreq>' . $changefreq . '</changefreq>';
        $url .= '<priority>' . $priority . '</priority>';
        $url .= '</url>';

        return $url;
    }

    /**
     * Generate robots.txt
     */
    public function robots()
    {
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /kecamatan/\n";
        $robots .= "Disallow: /api/\n\n";
        $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($robots, 200)
            ->header('Content-Type', 'text/plain');
    }
}
