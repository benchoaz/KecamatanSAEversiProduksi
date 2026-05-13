import pty
import os
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'
local_path = '/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/app/resources/views/landing.blade.php'
remote_tmp = '/tmp/landing.blade.php'

# Step 1: SCP to host
print("--- Step 1: SCP to Host ---")
pid, fd = pty.fork()
if pid == 0:
    os.execv('/usr/bin/scp', ['scp', '-o', 'StrictHostKeyChecking=no', local_path, f'{user}@{host}:{remote_tmp}'])
else:
    output = b""
    start = time.time()
    while time.time() - start < 60:
        try:
            chunk = os.read(fd, 4096)
            if not chunk: break
            output += chunk
            if b"password:" in chunk.lower():
                os.write(fd, (pw + "\n").encode())
        except:
            break
    print(output.decode(errors='ignore'))

# Step 2: Docker CP into container
print("--- Step 2: Docker CP to Container ---")
pid, fd = pty.fork()
if pid == 0:
    cmd = f"sudo docker cp {remote_tmp} kecamatan-app:/var/www/resources/views/landing.blade.php && sudo docker exec kecamatan-app php artisan view:clear"
    os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
else:
    output = b""
    start = time.time()
    while time.time() - start < 30:
        try:
            chunk = os.read(fd, 4096)
            if not chunk: break
            output += chunk
            if b"password:" in chunk.lower():
                os.write(fd, (pw + "\n").encode())
        except:
            break
    print(output.decode(errors='ignore'))
