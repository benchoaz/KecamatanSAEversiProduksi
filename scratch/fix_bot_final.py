import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_step(cmd, timeout=30):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', '-o', 'ConnectTimeout=10', f'{user}@{host}', cmd])
    else:
        output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < timeout:
            try:
                chunk = os.read(fd, 8192)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower() and not password_sent:
                    time.sleep(0.5)
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return output.decode(errors='ignore')

# 1. Upload the patched file
print("Uploading IntentHandler.php...")
local_file = '/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/app/app/Services/WhatsApp/IntentHandler.php'
with open(local_file, 'r') as f:
    content = f.read()

# Using a more robust way to write the file
write_cmd = f"cat << 'EOF' > /home/ubuntu/kecamatanSAE/app/app/Services/WhatsApp/IntentHandler.php\n{content}\nEOF"
run_step(write_cmd, timeout=60)

# 2. Clear AI Memories
print("Clearing AI memories...")
clear_cmd = "cd kecamatanSAE && docker compose exec -T db psql -U user -d dashboard_kecamatan -c \"DELETE FROM ai_memories;\""
run_step(clear_cmd)

# 3. Restart the app container to clear OPcache
print("Restarting app container...")
restart_cmd = "cd kecamatanSAE && docker compose restart app"
run_step(restart_cmd)

print("Done!")
