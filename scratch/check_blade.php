<?php
require 'app/vendor/autoload.php';
$app = require_once 'app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $view = view('kecamatan.settings.waha-n8n', [
        'settings' => App\Models\WahaN8nSetting::getSettings() ?? new App\Models\WahaN8nSetting,
        'providers' => [],
        'profile' => appProfile()
    ]);
    echo "Blade syntactically correct (compilation start)\n";
    $compiled = $app['view']->getEngineResolver()->resolve('blade')->getCompiler()->compileString(file_get_contents('app/resources/views/kecamatan/settings/waha-n8n.blade.php'));
    echo "Compilation successful\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . " LINE: " . $e->getLine() . "\n";
}
