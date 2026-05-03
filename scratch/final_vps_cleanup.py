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
        output = b""
        start = time.time()
        while time.time() - start < 120:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

print("--- Deep Cleaning & Pulling ---")
print(run_ssh_cmd("cd /home/ubuntu/kecamatanSAE && sudo rm -rf scratch/ && git reset --hard HEAD && git pull origin main"))

print("--- Final Permissions Check ---")
print(run_ssh_cmd("sudo -S chown -R ubuntu:ubuntu /home/ubuntu/kecamatanSAE"))
print(run_ssh_cmd("sudo -S docker exec kecamatan-app chown -R www-data:www-data /var/www/storage"))
print(run_ssh_cmd("sudo -S docker exec kecamatan-app chmod -R 775 /var/www/storage"))

print("--- DONE ---")
