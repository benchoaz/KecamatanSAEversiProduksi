import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_step(cmd, timeout=30):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', '-o', 'ConnectTimeout=10', f'{user}@{host}', cmd])
    else:
        output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < timeout:
            try:
                chunk = os.read(fd, 8192)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower() and not password_sent:
                    time.sleep(0.5)
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return output.decode(errors='ignore')

print("=" * 60)
print("CHECKING CORE DATA COUNTS")
print("=" * 60)
tinker_cmd = "cd kecamatanSAE && docker compose exec -T app php artisan tinker --execute=\"echo 'LAYANAN: ' . \\App\\Models\\PublicService::count() . PHP_EOL; echo 'FAQ: ' . \\App\\Models\\Faq::count() . PHP_EOL; echo 'MENU: ' . \\App\\Models\\NavMenu::count() . PHP_EOL;\""
print(run_step(tinker_cmd))
