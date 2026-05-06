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
print("FINDING LARGE FILES (>10MB) ON VPS")
print("=" * 60)
# Search for large files, excluding system dirs
print(run_step("sudo find / -type f -size +10M -not -path '/proc/*' -not -path '/sys/*' -not -path '/var/lib/docker/overlay2/*' 2>/dev/null | head -n 50"))

print("\n" + "=" * 60)
print("CHECKING FOR OTHER DOCKER PROJECTS")
print("=" * 60)
print(run_step("docker ps -a --format '{{.Labels}}' | grep 'com.docker.compose.project'"))
