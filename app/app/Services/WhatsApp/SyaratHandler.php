<?php

namespace App\Services\WhatsApp;

use App\Models\PelayananFaq;

class SyaratHandler
{
    protected \App\Services\FaqSearchService $faqSearchService;

    public function __construct(\App\Services\FaqSearchService $faqSearchService)
    {
        $this->faqSearchService = $faqSearchService;
    }

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

        // Search using unified FaqSearchService
        $data = $this->faqSearchService->search($query);

        if ($data['found']) {
            if (isset($data['multiple']) && $data['multiple']) {
                return [
                    'success' => true,
                    'intent' => 'syarat_suggestions',
                    'reply' => $this->formatSuggestions($data['results']),
                    'state_update' => null,
                ];
            }

            // Single match
            $top = $data['results'][0];
            return [
                'success' => true,
                'intent' => 'syarat',
                'reply' => $this->formatResultAnswer($top),
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
     * Format multiple suggestions into a numbered list for WhatsApp
     */
    protected function formatSuggestions(array $results): string
    {
        $reply = "Ditemukan beberapa topik yang relevan:\n\n";
        foreach ($results as $i => $res) {
            $num = $i + 1;
            $reply .= "{$num}. SYARAT " . strtoupper($res['question']) . "\n";
        }
        $reply .= "\nSilakan ketik kata kunci yang lebih spesifik dari pilihan di atas.\n";
        $reply .= "Ketik *MENU* untuk kembali.";
        return $reply;
    }

    /**
     * Format single FAQ result for WhatsApp
     */
    protected function formatResultAnswer(array $result): string
    {
        $title = $result['question'];
        $answer = $result['answer'];
        $baseUrl = $this->getPublicUrl();
        
        $reply = "✅ *{$title}*\n\n";
        $reply .= $answer;
        
        $link = $this->detectServiceLink($title);
        if ($link) {
            $reply .= "\n\nAjukan Online:\n";
            $reply .= "{$baseUrl}/{$link}";
        }
        
        $reply .= "\n\nKetik *SYARAT* untuk lainnya.\n";
        $reply .= "Ketik *MENU* atau *0* untuk kembali.";
        return $reply;
    }

    /**
     * Format FAQ answer for WhatsApp with link
     */
    protected function formatFaqAnswer(PelayananFaq $faq): string
    {
        $serviceLink = $this->detectServiceLink($faq->keywords);
        $baseUrl = $this->getPublicUrl();

        $reply = "{$faq->question}\n\n";
        $reply .= $faq->answer;

        if ($serviceLink) {
            $reply .= "\n\nAjukan Permohonan Online:\n";
            $reply .= "{$baseUrl}/{$serviceLink}";
        }

        $reply .= "\n\nKetik *SYARAT* untuk lainnya.\n";
        $reply .= "Ketik *MENU* atau *0* untuk kembali.";

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
        $baseUrl = $this->getPublicUrl();

        $reply = "🏛️ *SYARAT LAYANAN KECAMATAN*\n\n";
        $reply .= "Silakan ketik layanan yang Anda butuhkan:\n\n";

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

        $reply .= "Contoh: *syarat kk*, *syarat ktp*\n\n";
        $reply .= "Ajukan Secara Online:\n";
        $reply .= "{$baseUrl}/#layanan\n\n";
        $reply .= "Ketik *MENU* atau *0* untuk kembali.";

        return $reply;
    }

    /**
     * Get not found message with suggestions
     */
    protected function getNotFoundMessage(string $query): string
    {
        $baseUrl = $this->getPublicUrl();

        $reply = "Maaf, tidak ditemukan informasi syarat untuk \"{$query}\".\n\n";
        $reply .= "Silakan coba kata kunci lain seperti:\n";
        $reply .= "- SYARAT KTP\n";
        $reply .= "- SYARAT KK\n";
        $reply .= "- SYARAT AKTA\n\n";
        $reply .= "Lihat Semua Layanan:\n";
        $reply .= "{$baseUrl}/#layanan\n\n";
        $reply .= "Ketik *SYARAT* untuk daftar lengkap.\n";
        $reply .= "Ketik *MENU* atau *0* untuk kembali.";

        return $reply;
    }

    protected function getPublicUrl(): string
    {
        $profile = \App\Models\AppProfile::first();
        if ($profile && !empty($profile->public_url)) {
            return rtrim($profile->public_url, '/');
        }
        return rtrim(env('PUBLIC_BASE_URL', config('app.url')), '/');
    }
}
