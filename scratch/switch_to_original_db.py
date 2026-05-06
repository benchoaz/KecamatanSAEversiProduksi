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

print("=" * 60)
print("SWITCHING TO ORIGINAL DATABASE VOLUME")
print("=" * 60)
# Change the external volume names to the ones that likely contain the data
fix_cmd = """
cd kecamatanSAE && 
sed -i 's/name: kecamatansae_pgdata/name: kecamatansaeversikabupaten_pgdata/g' docker-compose.yml &&
sed -i 's/name: kecamatansae_redis_data/name: kecamatansaeversikabupaten_redis_data/g' docker-compose.yml &&
docker compose down && 
docker compose up -d
"""
print(run_step(fix_cmd, timeout=60))

print("\n" + "=" * 60)
print("VERIFYING DATA IS BACK")
print("=" * 60)
verify_cmd = r"""
cd kecamatanSAE && docker compose exec -T app php artisan tinker --execute="echo 'JUMLAH USER: ' . \\App\\Models\\User::count();"
"""
print(run_step(verify_cmd))
