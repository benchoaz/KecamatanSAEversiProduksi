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
        while time.time() - start < 30: # 30s timeout
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return output.decode(errors='ignore')

print("--- CHECKING VPS TOKENS (V2) ---")
phone = '82231203765'
tinker_cmd = f"""
\$tokens = \\App\\Models\\PortalLoginToken::where('phone', 'like', '%{phone}%')->latest()->limit(5)->get();
foreach (\$tokens as \$t) {{
    echo 'SIG: ' . substr(\$t->signature, 0, 10) . '... UsedAt=' . (\$t->used_at ? \$t->used_at->toDateTimeString() : 'NULL') . ', Expired=' . (\$t->expires_at->isPast() ? 'YES' : 'NO') . ', Created=' . \$t->created_at->toDateTimeString() . PHP_EOL;
}}
"""
tinker_cmd_escaped = tinker_cmd.replace('"', '\\"').replace('\n', ' ')
cmd = f"cd kecamatanSAE && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan tinker --execute=\"{tinker_cmd_escaped}\""
print(run_step(cmd))
