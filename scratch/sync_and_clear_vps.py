
import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_ssh_cmd(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        password_sent = False
        start = time.time()
        output = b""
        while time.time() - start < 60:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b'password:' in chunk.lower() and not password_sent:
                    os.write(fd, (pw + '\n').encode())
                    password_sent = True
            except:
                break
        return output.decode()

print("--- Step 1: Clearing Caches ---")
cmds = [
    "docker exec kecamatan-app php artisan view:clear",
    "docker exec kecamatan-app php artisan cache:clear",
    "docker exec kecamatan-app php artisan config:clear",
    "docker restart kecamatan-app"
]

for cmd in cmds:
    print(f"Running: {cmd}")
    res = run_ssh_cmd(cmd)
    # print(res) # Avoid too much output

print("--- Step 2: Verifying File Path ---")
verify_cmd = "docker exec kecamatan-app ls -l resources/views/desa/administrasi/personil/create.blade.php"
res = run_ssh_cmd(verify_cmd)
print(res)
