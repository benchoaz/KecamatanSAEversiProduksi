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

output = run_step("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan tinker --execute=\"echo json_encode(\\App\\Models\\AppProfile::first()->only(['is_ai_active', 'ai_provider', 'openai_api_key']));\"")
print(output)
