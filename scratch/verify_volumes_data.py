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

def check_volume(vol_name):
    print(f"\n--- CHECKING VOLUME: {vol_name} ---")
    # We temporarily switch docker-compose to use this volume and then query it
    switch_cmd = f"""
cd kecamatanSAE && 
sed -i 's/name: .*_pgdata/name: {vol_name}/g' docker-compose.yml &&
docker compose down && 
docker compose up -d db &&
sleep 5 &&
docker compose exec -T db psql -U user -d dashboard_kecamatan -c "SELECT count(*) FROM users;"
"""
    return run_step(switch_cmd, timeout=60)

# We check both
print(check_volume("kecamatansae_pgdata"))
print(check_volume("kecamatansaeversikabupaten_pgdata"))
