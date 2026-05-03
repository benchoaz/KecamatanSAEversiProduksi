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
        while time.time() - start < 15:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

print("Testing Save via Tinker...")
# Escaping $p with \$p and using double backslashes for PHP classes
test_cmd = "sudo -S docker exec -i kecamatan-app php artisan tinker --execute=\"\\$p = \\\\App\\\\Models\\\\AppProfile::first(); \\$p->openai_api_key = 'sk-test-connection-success'; \\$p->save(); echo 'SAVED_VALUE: ' . \\$p->openai_api_key;\""
print(run_step(test_cmd))

