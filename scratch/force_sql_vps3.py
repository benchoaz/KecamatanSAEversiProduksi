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

# Write php script on VPS
php_content = """<?php
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

if (!Schema::hasColumn('app_profiles', 'ai_provider')) {
    Schema::table('app_profiles', function (Blueprint \\$table) {
        \\$table->string('ai_provider')->default('gemini');
        \\$table->text('openai_api_key')->nullable();
        \\$table->text('google_api_key')->nullable();
        \\$table->text('anthropic_api_key')->nullable();
        \\$table->text('xai_api_key')->nullable();
        \\$table->text('deepseek_api_key')->nullable();
        \\$table->text('dashscope_api_key')->nullable();
        \\$table->text('zhipu_api_key')->nullable();
        \\$table->text('openrouter_api_key')->nullable();
        \\$table->text('alpha_vantage_api_key')->nullable();
    });
    echo "COLUMNS ADDED SUCCESSFULLY";
} else {
    echo "COLUMNS ALREADY EXIST";
}
"""
print(run_step(f"cat << 'INNEREOF' > fix_db.php\n{php_content}\nINNEREOF"))

# Copy to docker and execute
print(run_step("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml cp fix_db.php app:/var/www/fix_db.php"))
print(run_step("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan tinker fix_db.php"))

