import os
import pty
import sys
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_remote(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        full_output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < 30:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                full_output += chunk
                if b"password:" in chunk.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return full_output.decode(errors='ignore')

if len(sys.argv) > 1:
    print(run_remote(sys.argv[1]))
else:
    print("Usage: python3 vps_exec.py \"command\"")
