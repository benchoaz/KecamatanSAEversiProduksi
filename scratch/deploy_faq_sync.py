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
os.system("git commit -m 'Add FAQ sync command and seed general administrative FAQs'")
os.system("git push origin main")

print("--- Pulling on VPS ---")
print(run_ssh_cmd("cd /home/ubuntu/kecamatanSAE && git pull origin main"))

print("--- Running FAQ Sync on VPS ---")
print(run_ssh_cmd("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan faq:sync-services --clear"))

print("--- DONE ---")
