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
print("RESETTING SUPERADMIN PASSWORD")
print("=" * 60)
tinker_cmd = r"""
cd kecamatanSAE && docker compose exec -T app php artisan tinker --execute="
\$admin = \\App\\Models\\User::where('username', 'superadmin')->first();
if (\$admin) {
    \$admin->password = \\Hash::make('password');
    \$admin->save();
    echo 'BERHASIL: Password superadmin adalah password' . PHP_EOL;
} else {
    echo 'GAGAL: User superadmin tidak ditemukan!' . PHP_EOL;
}
"
"""
print(run_step(tinker_cmd))

print("\n" + "=" * 60)
print("CHECKING DATABASE PROFILES (FOR IMAGES)")
print("=" * 60)
tinker_cmd_images = r"""
cd kecamatanSAE && docker compose exec -T app php artisan tinker --execute="
\$profile = \\App\\Models\\AppProfile::first();
if (\$profile) {
    echo 'LOGO_PATH: ' . \$profile->logo_path . PHP_EOL;
    echo 'HERO_PATH: ' . \$profile->hero_image_path . PHP_EOL;
} else {
    echo 'Profile tidak ditemukan!' . PHP_EOL;
}
"
"""
print(run_step(tinker_cmd_images))

print("\n" + "=" * 60)
print("FIXING STORAGE SYMLINK AGAIN")
print("=" * 60)
print(run_step("cd kecamatanSAE && docker compose exec -T app sh -c 'rm -rf public/storage && php artisan storage:link'"))
