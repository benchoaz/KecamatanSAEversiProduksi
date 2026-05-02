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
        while time.time() - start < 300: # 5 min timeout
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                print(chunk.decode(errors='ignore'), end='', flush=True)
                if b"password:" in chunk.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return output.decode(errors='ignore')

print("--- STEP 1: Update ---")
run_step("sudo apt-get update")

print("--- STEP 2: Install Curl/Git ---")
run_step("sudo apt-get install -y curl git")

print("--- STEP 3: Install Docker ---")
run_step("curl -fsSL https://get.docker.com -o get-docker.sh && sudo sh get-docker.sh")

print("--- STEP 4: Clone Repo ---")
run_step("git clone https://github.com/benchoaz/KecamatanSAEversiProduksi.git kecamatanSAE")

print("--- STEP 5: Prepare .env ---")
run_step("cd kecamatanSAE && cp app/.env.example app/.env")
