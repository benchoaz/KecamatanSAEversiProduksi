<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\WhatsApp\AiHandler;
use App\Services\FaqSearchService;

$faq = app(FaqSearchService::class);
$ai = app(AiHandler::class);

// Simulate building knowledge base
$knowledge = \Cache::remember('whatsapp_ai_knowledge', 600, function() use ($faq) {
    return $faq->buildKnowledgeBase();
});

echo "--- KNOWLEDGE BASE SENT TO AI ---\n";
echo $knowledge;
echo "\n--- END ---\n";
