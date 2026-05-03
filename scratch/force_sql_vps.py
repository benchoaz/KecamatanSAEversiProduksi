import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_step(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        start = time.time()
        while time.time() - start < 15:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

# 1. Clean the fake migration record
print("Cleaning migrations table...")
run_step("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan tinker --execute=\"DB::table('migrations')->where('migration', 'like', '%2026_05_03%')->delete();\"")

# 2. Add columns directly using Schema Builder
print("\nInjecting columns directly into database...")
php_code = """
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

if (!Schema::hasColumn('app_profiles', 'ai_provider')) {
    Schema::table('app_profiles', function (Blueprint $table) {
        $table->string('ai_provider')->default('gemini');
        $table->text('openai_api_key')->nullable();
        $table->text('google_api_key')->nullable();
        $table->text('anthropic_api_key')->nullable();
        $table->text('xai_api_key')->nullable();
        $table->text('deepseek_api_key')->nullable();
        $table->text('dashscope_api_key')->nullable();
        $table->text('zhipu_api_key')->nullable();
        $table->text('openrouter_api_key')->nullable();
        $table->text('alpha_vantage_api_key')->nullable();
    });
    echo 'Columns successfully added.';
} else {
    echo 'Columns already exist.';
}
"""
print(run_step(f"sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan tinker --execute=\"{php_code}\""))

