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
print("CHECKING USERS IN DATABASE")
print("=" * 60)
# Use artisan tinker to query safely without worrying about psql credentials
tinker_cmd = """
cd kecamatanSAE && docker compose exec -T app php artisan tinker --execute="
\$users = \App\Models\User::with(['role', 'desa'])->get();
foreach(\$users as \$u) {
    echo 'ID: ' . \$u->id . ' | Name: ' . \$u->name . ' | Email: ' . \$u->email . ' | Role: ' . (\$u->role->name ?? '-') . ' | Desa: ' . (\$u->desa->nama_desa ?? '-') . PHP_EOL;
}
"
"""
print(run_step(tinker_cmd))

print("\n" + "=" * 60)
print("CHECKING PERSONIL PHOTOS IN DATABASE AND STORAGE")
print("=" * 60)
tinker_cmd_photo = """
cd kecamatanSAE && docker compose exec -T app php artisan tinker --execute="
\$personils = \App\Models\PersonilDesa::whereNotNull('foto')->take(5)->get();
foreach(\$personils as \$p) {
    echo 'Nama: ' . \$p->nama . ' | Foto DB: ' . \$p->foto . PHP_EOL;
}
"
"""
print(run_step(tinker_cmd_photo))

print("\n" + "=" * 60)
print("CHECKING IF PHOTOS ACTUALLY EXIST ON DISK")
print("=" * 60)
print(run_step("cd kecamatanSAE && docker compose exec -T app find storage/app -type f -name '*.jpg' -o -name '*.png' -o -name '*.jpeg' | head -n 15"))

