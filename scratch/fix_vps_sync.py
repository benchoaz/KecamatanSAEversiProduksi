
import os
import pty
import time
import sys

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_remote_command(command):
    print(f"Executing: {command}")
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', command])
    else:
        # Wait for password prompt
        output = b""
        start_time = time.time()
        while time.time() - start_time < 10:
            try:
                data = os.read(fd, 1024)
                output += data
                if b"password:" in data.lower():
                    os.write(fd, (pw + '\n').encode())
                    break
            except OSError:
                break
        
        # Capture the rest of the output
        result = b""
        start_time = time.time()
        while time.time() - start_time < 20:
            try:
                data = os.read(fd, 1024)
                if not data:
                    break
                result += data
            except OSError:
                break
        
        print(result.decode(errors='ignore'))

commands = [
    "cd kecamatanSAE && git fetch origin && git reset --hard origin/main",
    "cd kecamatanSAE && docker compose exec -T app php artisan migrate --force",
    "cd kecamatanSAE && docker compose exec -T app php artisan view:clear",
    "cd kecamatanSAE && docker compose exec -T app php artisan cache:clear",
    "cd kecamatanSAE && grep 'service' app/resources/views/kecamatan/announcements/create.blade.php"
]

for cmd in commands:
    run_remote_command(cmd)
