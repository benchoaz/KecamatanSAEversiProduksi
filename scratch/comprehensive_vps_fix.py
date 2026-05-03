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
        while time.time() - start < 60:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

print("--- Force Re-deploying View & Clearing Cache ---")
print(run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan view:clear"))
print(run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan optimize:clear"))

print("--- Fixing Storage Symlink (Relative) ---")
print(run_ssh_cmd("sudo -S docker exec kecamatan-app rm -f /var/www/public/storage"))
print(run_ssh_cmd("sudo -S docker exec kecamatan-app ln -snf ../storage/app/public /var/www/public/storage"))

print("--- Fixing Permissions (Again, Thoroughly) ---")
print(run_ssh_cmd("sudo -S docker exec kecamatan-app chown -R www-data:www-data /var/www/storage"))
print(run_ssh_cmd("sudo -S docker exec kecamatan-app chmod -R 775 /var/www/storage"))

print("--- Checking Logo File Existence ---")
print(run_ssh_cmd("sudo -S docker exec kecamatan-app ls -R /var/www/storage/app/public/logos"))

print("--- DONE ---")
