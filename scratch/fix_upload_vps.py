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

print("--- Pushing to GitHub ---")
os.system("git add .")
os.system("git commit -m 'Fix missing enctype in profile settings form'")
os.system("git push origin main")

print("--- Pulling on VPS ---")
print(run_ssh_cmd("cd /home/ubuntu/kecamatanSAE && git pull origin main"))

print("--- Force Copying Profile View ---")
src = "/home/ubuntu/kecamatanSAE/app/resources/views/kecamatan/settings/profile.blade.php"
dest = "kecamatan-app:/var/www/resources/views/kecamatan/settings/profile.blade.php"
print(run_ssh_cmd(f"sudo -S docker cp {src} {dest}"))

print("--- Checking & Fixing Storage Permissions ---")
print(run_ssh_cmd("sudo -S docker exec -T kecamatan-app chown -R www-data:www-data /var/www/storage"))
print(run_ssh_cmd("sudo -S docker exec -T kecamatan-app chmod -R 775 /var/www/storage"))

print("--- DONE ---")
