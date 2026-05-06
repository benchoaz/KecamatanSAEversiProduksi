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
        password_sent = False
        start = time.time()
        while time.time() - start < 30: 
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return output.decode(errors='ignore')

print("--- FIXING PERMISSIONS ---")
print(run_step("cd kecamatanSAE && docker compose exec -T kecamatan-app chmod -R 775 storage bootstrap/cache && docker compose exec -T kecamatan-app chown -R www-data:www-data storage bootstrap/cache"))

print("\n--- CHECKING ERROR LOGS FOR ALAS SUMUR LOR ---")
print(run_step("cd kecamatanSAE && docker compose exec -T kecamatan-app tail -n 50 storage/logs/laravel.log | grep -i 'error'"))
