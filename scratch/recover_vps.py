import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_step(cmd, timeout=60):
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
print("STEP 1: Tunggu container benar-benar siap (15 detik)...")
print("=" * 60)
time.sleep(15)
result = run_step("cd kecamatanSAE && docker compose ps")
print(result)

print("\n" + "=" * 60)
print("STEP 2: Fix Storage Permissions")
print("=" * 60)
result = run_step("cd kecamatanSAE && docker compose exec -T app sh -c 'chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache && echo PERMISSIONS_OK'")
print(result)

print("\n" + "=" * 60)
print("STEP 3: Baca Log Error Terbaru (50 baris)")
print("=" * 60)
result = run_step("cd kecamatanSAE && docker compose exec -T app tail -n 80 storage/logs/laravel.log")
print(result)

print("\n" + "=" * 60)
print("STEP 4: Cek user alassumurlor di database")
print("=" * 60)
result = run_step("cd kecamatanSAE && docker compose exec -T db psql -U postgres -d kecamatan_sae -c \"SELECT u.id, u.name, u.email, r.name as role, d.nama_desa FROM users u LEFT JOIN roles r ON u.role_id = r.id LEFT JOIN desa d ON u.desa_id = d.id WHERE u.name ILIKE '%alas%' OR u.email ILIKE '%alas%';\"")
print(result)

print("\n" + "=" * 60)
print("STEP 5: Cek disk usage VPS")
print("=" * 60)
result = run_step("df -h && du -sh kecamatanSAE/app/storage/app/")
print(result)

print("\nDONE!")
