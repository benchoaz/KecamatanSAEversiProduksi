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
print("LISTING ALL USERS IN DATABASE")
print("=" * 60)
tinker_all_users = r"""
cd kecamatanSAE && docker compose exec -T app php artisan tinker --execute="
\$users = \\App\\Models\\User::all();
foreach(\$users as \$u) {
    echo 'Username: ' . \$u->username . ' | Role ID: ' . \$u->role_id . PHP_EOL;
}
if (\$users->isEmpty()) {
    echo 'DATABASE USERS KOSONG!';
}
"
"""
print(run_step(tinker_all_users))

print("\n" + "=" * 60)
print("CHECKING STORAGE APP CONTENTS")
print("=" * 60)
print(run_step("cd kecamatanSAE && docker compose exec -T app ls -R storage/app | grep -v '^\.' | head -n 50"))
