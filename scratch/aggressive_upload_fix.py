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

print("--- Aggressive Host Cleanup ---")
# Reset everything to ubuntu owner so git can work
print(run_ssh_cmd("sudo -S chown -R ubuntu:ubuntu /home/ubuntu/kecamatanSAE"))
print(run_ssh_cmd("cd /home/ubuntu/kecamatanSAE && git reset --hard HEAD && git clean -fd && git pull origin main"))

print("--- Pushing Code to Container ---")
# Using docker cp to ensure the fixed view is there
src = "/home/ubuntu/kecamatanSAE/app/resources/views/kecamatan/settings/profile.blade.php"
dest = "kecamatan-app:/var/www/resources/views/kecamatan/settings/profile.blade.php"
print(run_ssh_cmd(f"sudo -S docker cp {src} {dest}"))

print("--- Fixing Runtime Permissions inside Container ---")
# Grant www-data ownership inside container for storage
print(run_ssh_cmd("sudo -S docker exec kecamatan-app chown -R www-data:www-data /var/www/storage /var/www/public"))
print(run_ssh_cmd("sudo -S docker exec kecamatan-app chmod -R 775 /var/www/storage /var/www/public"))

print("--- Link Storage (Just in case) ---")
print(run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan storage:link"))

print("--- Optimize Clear ---")
print(run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan optimize:clear"))

print("--- DONE ---")
