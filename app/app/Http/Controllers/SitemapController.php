<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\Umkm;
use App\Models\WorkDirectory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    /**
     * Generate XML Sitemap — SEO Optimized
     * Mencakup: homepage, layanan, desa, berita, UMKM, statistik, tracking
     */
    public function index()
    {
        $sitemap = Cache::remember('sitemap_xml_data', now()->addHours(6), function () {
            $lines = [];
            $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
            $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
                     . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

            // === HALAMAN UTAMA ===
            $lines[] = $this->addUrl(url('/'), '1.0', 'daily');
            $lines[] = $this->addUrl(url('/tracking'), '0.9', 'weekly');
            $lines[] = $this->addUrl(url('/berita'), '0.8', 'daily');

            // === STATISTIK ===
            $statistikRoutes = [
                'landing.statistik.index'        => 'statistik',
                'landing.statistik.pendidikan'   => 'statistik/pendidikan',
                'landing.statistik.pekerjaan'    => 'landing.statistik.pekerjaan',
                'landing.statistik.agama'        => 'landing.statistik.agama',
                'landing.statistik.kesehatan'    => 'landing.statistik.kesehatan',
                'landing.statistik.kesejahteraan'=> 'landing.statistik.kesejahteraan',
            ];
            foreach ($statistikRoutes as $routeName => $fallback) {
                try {
                    $url = route($routeName);
                    $lines[] = $this->addUrl($url, '0.7', 'weekly');
                } catch (\Exception $e) {
                    // Route tidak terdaftar, skip
                }
            }

            // === ECONOMY / JASA ===
            try {
                $lines[] = $this->addUrl(route('economy.index', ['tab' => 'jasa']), '0.8', 'weekly');
                $lines[] = $this->addUrl(route('economy.index', ['tab' => 'umkm']), '0.8', 'weekly');
            } catch (\Exception $e) {}

            // === DESA (dengan gambar jika ada) ===
            try {
                $desas = Desa::orderBy('nama_desa')->get();
                foreach ($desas as $desa) {
                    $url = url('/desa/' . $desa->id);
                    $lines[] = $this->addUrl($url, '0.7', 'monthly', $desa->updated_at);
                }
            } catch (\Exception $e) {}

            // === BERITA ===
            try {
                $beritas = Berita::published()->orderByDesc('created_at')->take(200)->get();
                foreach ($beritas as $berita) {
                    try {
                        $url = route('public.berita.show', $berita->slug);
                        $lines[] = $this->addUrl($url, '0.6', 'monthly', $berita->updated_at);
                    } catch (\Exception $e) {}
                }
            } catch (\Exception $e) {}

            // === UMKM ===
            try {
                $umkms = Umkm::where('status', Umkm::STATUS_AKTIF)->orderByDesc('updated_at')->take(100)->get();
                foreach ($umkms as $umkm) {
                    try {
                        $url = route('umkm_rakyat.show', $umkm->slug ?? $umkm->id);
                        $lines[] = $this->addUrl($url, '0.6', 'weekly', $umkm->updated_at);
                    } catch (\Exception $e) {}
                }
            } catch (\Exception $e) {}

            // === WORK DIRECTORY ===
            try {
                $workItems = WorkDirectory::public()->orderByDesc('updated_at')->take(100)->get();
                foreach ($workItems as $work) {
                    try {
                        $url = route('economy.show', $work->id);
                        $lines[] = $this->addUrl($url, '0.6', 'weekly', $work->updated_at);
                    } catch (\Exception $e) {}
                }
            } catch (\Exception $e) {}

            $lines[] = '</urlset>';

            return implode("\n", $lines);
        });

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=21600'); // 6 jam cache publik
    }

    /**
     * Generate robots.txt — SEO & Privacy Optimized
     */
    public function robots()
    {
        $appUrl = config('app.url', url('/'));

        $robots  = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /kecamatan/\n";
        $robots .= "Disallow: /desa/\n";       // Dashboard desa internal
        $robots .= "Disallow: /api/\n";
        $robots .= "Disallow: /hub/\n";
        $robots .= "Disallow: /login\n";
        $robots .= "Disallow: /logout\n";
        $robots .= "Disallow: /profile\n";
        $robots .= "Disallow: /receipt/\n";
        $robots .= "\n";
        $robots .= "# Allow public pages\n";
        $robots .= "Allow: /berita/\n";
        $robots .= "Allow: /umkm/\n";
        $robots .= "Allow: /statistik/\n";
        $robots .= "Allow: /tracking\n";
        $robots .= "\n";
        $robots .= "Sitemap: {$appUrl}/sitemap.xml\n";

        return response($robots, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Helper: Build <url> element for sitemap
     */
    private function addUrl(string $loc, string $priority = '0.5', string $changefreq = 'weekly', $lastmod = null): string
    {
        $lastmodDate = $lastmod
            ? (is_string($lastmod) ? $lastmod : $lastmod->format('Y-m-d'))
            : now()->format('Y-m-d');

        return "<url>"
            . "<loc>" . htmlspecialchars($loc) . "</loc>"
            . "<lastmod>{$lastmodDate}</lastmod>"
            . "<changefreq>{$changefreq}</changefreq>"
            . "<priority>{$priority}</priority>"
            . "</url>";
    }
}
