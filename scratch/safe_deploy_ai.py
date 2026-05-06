import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_step(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < 60: # 1 min timeout
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return output.decode(errors='ignore')

print("--- RE-DEPLOYING AI (SAFE MODE) ---")
# 1. Stash changes
print("Step 1: Stashing local changes on VPS...")
run_step("cd kecamatanSAE && git stash")

# 2. Pull from Main
print("Step 2: Pulling latest code from GitHub...")
run_step("cd kecamatanSAE && git pull origin main")

# 3. Clear Cache
print("Step 3: Clearing Laravel Cache & AI Knowledge...")
run_step("cd kecamatanSAE/app && php artisan optimize:clear && php artisan cache:forget whatsapp_ai_knowledge")

print("--- DEPLOY COMPLETE! AI IS NOW SMARTER ---")
