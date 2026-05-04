import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_ssh_cmd(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        start = time.time()
        while time.time() - start < 60:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

print("--- Checking Docker Containers on VPS ---")
print(run_ssh_cmd("sudo -S docker ps"))

print("\n--- Checking WAHA Session Config ---")
# Replace with actual container name if different
print(run_ssh_cmd("sudo -S docker exec waha curl -s http://localhost:3000/api/sessions/default"))
