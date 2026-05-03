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
os.system("git commit -m 'Remove logo-icon background color'")
os.system("git push origin main")

print("--- Pulling on VPS ---")
print(run_ssh_cmd("cd /home/ubuntu/kecamatanSAE && git pull origin main"))

print("--- Force Copying Updated CSS to Container ---")
src = "/home/ubuntu/kecamatanSAE/app/public/css/dashboard.css"
dest = "kecamatan-app:/var/www/public/css/dashboard.css"
print(run_ssh_cmd(f"sudo -S docker cp {src} {dest}"))

print("--- Clearing Cache on VPS ---")
print(run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan optimize:clear"))

print("--- DONE ---")
