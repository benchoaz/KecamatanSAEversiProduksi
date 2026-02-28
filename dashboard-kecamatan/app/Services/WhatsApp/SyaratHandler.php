<?php

namespace App\Services\WhatsApp;

use App\Models\PelayananFaq;

class SyaratHandler
{
    /**
     * Search for requirements/syarat based on query
     */
    public function search(string $query): array
    {
        $query = trim(strtolower($query));
        \Log::info('SyaratHandler searching for: ' . $query);

        // If empty query, show available categories
        if (empty($query)) {
            return [
                'success' => true,
                'intent' => 'syarat_list',
                'reply' => $this->getCategoriesList(),
                'state_update' => null,
            ];
        }

        // Search FAQ for matching keywords
        $faq = $this->findMatchingFaq($query);

        if ($faq) {
            return [
                'success' => true,
                'intent' => 'syarat',
                'reply' => $this->formatFaqAnswer($faq),
                'state_update' => null,
            ];
        }

        // No match found - show suggestions
        return [
            'success' => true,
            'intent' => 'syarat_not_found',
            'reply' => $this->getNotFoundMessage($query),
            'state_update' => null,
        ];
    }

    /**
     * Find matching FAQ from database
     */
    protected function findMatchingFaq(string $query): ?PelayananFaq
    {
        // 1. Exact match in keywords (with commas) or question
        $faq = PelayananFaq::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(keywords) LIKE ?', ["%{$query}%"])
                    ->orWhereRaw('LOWER(question) LIKE ?', ["%{$query}%"]);
            })
            ->orderBy('priority', 'desc')
            ->first();

        if ($faq)
            return $faq;

        // 2. Try matching individual words if the query has multiple words
        $words = explode(' ', $query);
        if (count($words) > 1) {
            foreach ($words as $word) {
                if (strlen($word) < 3)
                    continue; // Skip short words
                $faq = PelayananFaq::where('is_active', true)
                    ->whereRaw('LOWER(keywords) LIKE ?', ["%{$word}%"])
                    ->orderBy('priority', 'desc')
                    ->first();
                if ($faq)
                    return $faq;
            }
        }

        // 3. Last resort: Fuzzy match - only check top 50 by priority to avoid O(N) lag
        $faqs = PelayananFaq::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->limit(50)
            ->get();

        foreach ($faqs as $faq) {
            $keywords = explode(',', strtolower($faq->keywords));
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword))
                    continue;
                if (str_contains($query, $keyword) || str_contains($keyword, $query)) {
                    return $faq;
                }
            }
        }

        return null;
    }

    /**
     * Format FAQ answer for WhatsApp with link
     */
    protected function formatFaqAnswer(PelayananFaq $faq): string
    {
        // Detect service keyword to build link
        $serviceLink = $this->detectServiceLink($faq->keywords);
        $baseUrl = env('PUBLIC_BASE_URL', config('app.url', 'https://babette-nonslanderous-randi.ngrok-free.dev'));

        $reply = "{$faq->question}\n\n";
        $reply .= $faq->answer;

        // Add link if service detected
        if ($serviceLink) {
            $reply .= "\n\nAjukan Permohonan Online:\n";
            $reply .= "{$baseUrl}/{$serviceLink}";
        }

        $reply .= "\n\nKetik *SYARAT* untuk melihat daftar layanan lainnya.\n";
        $reply .= "Ketik *MENU* untuk kembali ke menu utama.";

        return $reply;
    }

    /**
     * Detect service link from keywords
     */
    protected function detectServiceLink(string $keywords): ?string
    {
        $serviceLinks = [
            'ktp' => 'ktp',
            'kartu tanda penduduk' => 'ktp',
            'kk' => 'kk',
            'kartu keluarga' => 'kk',
            'akta' => 'akta',
            'akta lahir' => 'akta',
            'kelahiran' => 'akta',
            'sktm' => 'sktm',
            'tidak mampu' => 'sktm',
            'domisili' => 'domisili',
            'nikah' => 'nikah',
            'bpjs' => 'bpjs',
        ];

        $keywordsLower = strtolower($keywords);
        foreach ($serviceLinks as $keyword => $link) {
            if (str_contains($keywordsLower, $keyword)) {
                return $link;
            }
        }

        return null;
    }

    /**
     * Get list of available categories
     */
    protected function getCategoriesList(): string
    {
        $baseUrl = env('PUBLIC_BASE_URL', config('app.url', 'https://babette-nonslanderous-randi.ngrok-free.dev'));

        $reply = "SYARAT LAYANAN KECAMATAN\n\n";
        $reply .= "Silakan ketik layanan yang Anda butuhkan:\n\n";

        // Get all active FAQs grouped by category
        $faqs = PelayananFaq::where('is_active', true)
            ->where('category', '!=', 'Darurat')
            ->orderBy('category')
            ->orderBy('priority', 'desc')
            ->get();

        if ($faqs->isEmpty()) {
            $reply .= "Belum ada layanan tersedia.\n";
        } else {
            $grouped = $faqs->groupBy('category');

            foreach ($grouped as $category => $items) {
                $reply .= "*{$category}:*\n";
                foreach ($items as $faq) {
                    $keywords = explode(',', $faq->keywords);
                    $mainKeyword = trim($keywords[0] ?? '');
                    $reply .= "- SYARAT {$mainKeyword}\n";
                }
                $reply .= "\n";
            }
        }

        $reply .= "Contoh: *syarat kk*, *syarat ktp*, *syarat domisili*\n\n";
        $reply .= "Ajukan Secara Online:\n";
        $reply .= "{$baseUrl}/#layanan\n\n";
        $reply .= "Ketik *MENU* untuk kembali ke menu utama.";

        return $reply;
    }

    /**
     * Get not found message with suggestions
     */
    protected function getNotFoundMessage(string $query): string
    {
        $baseUrl = env('PUBLIC_BASE_URL', config('app.url', 'https://babette-nonslanderous-randi.ngrok-free.dev'));

        $reply = "Maaf, tidak ditemukan informasi syarat untuk \"{$query}\".\n\n";
        $reply .= "Silakan coba kata kunci lain seperti:\n";
        $reply .= "- SYARAT KTP\n";
        $reply .= "- SYARAT KK\n";
        $reply .= "- SYARAT AKTA\n";
        $reply .= "- SYARAT DOMISILI\n\n";
        $reply .= "Lihat Semua Layanan:\n";
        $reply .= "{$baseUrl}/#layanan\n\n";
        $reply .= "Ketik *SYARAT* untuk melihat daftar lengkap.";
        $reply .= "Ketik *MENU* untuk kembali ke menu utama.";

        return $reply;
    }
}
