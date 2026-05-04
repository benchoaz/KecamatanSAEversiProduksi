import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'
app_path = '/home/ubuntu/kecamatanSAE/app'

# Path file di lokal (workspace)
local_file = '/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/app/app/Services/WhatsApp/StatusHandler.php'
# Path file di VPS
remote_file = f'{app_path}/app/Services/WhatsApp/StatusHandler.php'

def push_file(local_path, remote_path):
    print(f"🚀 Pushing {os.path.basename(local_path)} to VPS...")
    with open(local_path, 'r') as f:
        content = f.read().replace("'", "'\\''")
    
    cmd = f"cat << 'EOF' > {remote_path}\n{content}\nEOF"
    
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        password_sent = False
        start = time.time()
        while time.time() - start < 30:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                text = chunk.decode(errors='ignore')
                if "password:" in text.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break

print("--- PUSHING UPDATED STATUS HANDLER (WITH LINK) ---")
push_file(local_file, remote_file)

# Restart App to be sure
pid, fd = pty.fork()
if pid == 0:
    os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', f"cd {app_path} && docker compose restart app"])
else:
    password_sent = False
    start = time.time()
    while time.time() - start < 60:
        try:
            chunk = os.read(fd, 4096)
            if not chunk: break
            if b"password:" in chunk.lower() and not password_sent:
                os.write(fd, (pw + "\n").encode())
                password_sent = True
        except:
            break

print("\n✅ STATUS HANDLER UPDATED! Please test 'Cek Berkas' again.")
