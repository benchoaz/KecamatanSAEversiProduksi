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

print("--- Verifying Layout inside Container ---")
print(run_ssh_cmd("sudo -S docker exec kecamatan-app grep -A 5 '.logo-icon {' /var/www/resources/views/layouts/kecamatan.blade.php"))

print("--- Checking Storage Link ---")
print(run_ssh_cmd("sudo -S docker exec kecamatan-app ls -la /var/www/public/storage"))

print("--- Checking Files in Storage ---")
print(run_ssh_cmd("sudo -S docker exec kecamatan-app ls -la /var/www/storage/app/public/logos"))

print("--- DONE ---")
