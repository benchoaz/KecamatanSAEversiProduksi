
<?php
$logFile = '/var/www/storage/logs/laravel.log';
$content = file_get_contents($logFile);
$lines = explode("\n", $content);
$buffer = [];
$capturing = false;

foreach ($lines as $line) {
    if (strpos($line, 'RAW_WHATSAPP_REQUEST') !== false) {
        $buffer = [$line];
        $capturing = true;
    } elseif ($capturing) {
        $buffer[] = $line;
        if (strpos($line, ']') === 0) { // end of array dump
            $capturing = false;
            $block = implode("\n", $buffer);
            if (strpos($block, '90735512682536') !== false) {
                echo "FOUND BLOCK:\n$block\n\n";
                break;
            }
        }
    }
}
