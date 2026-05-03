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
        start = time.time()
        while time.time() - start < 60: # 1 min timeout
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                print(chunk.decode(errors='ignore'), end='', flush=True)
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

print("--- FIXING LARAVEL PERMISSIONS ON VPS ---")
run_step("cd kecamatanSAE/app && sudo -S chmod -R 777 storage bootstrap/cache && sudo -S chmod -R 777 storage/logs/")
