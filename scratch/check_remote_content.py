import pty
import os
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'
cmd = 'sudo docker exec kecamatan-app grep -A 20 "WhatsApp Slim Banner" /var/www/resources/views/landing.blade.php'

pid, fd = pty.fork()
if pid == 0:
    os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
else:
    output = b""
    start = time.time()
    while time.time() - start < 30:
        try:
            chunk = os.read(fd, 4096)
            if not chunk: break
            output += chunk
            if b"password:" in chunk.lower():
                os.write(fd, (pw + "\n").encode())
        except:
            break
    print(output.decode(errors='ignore'))
