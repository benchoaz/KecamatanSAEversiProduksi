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
print("CHECKING ALL RUNNING CONTAINERS AND THEIR PROJECTS")
print("=" * 60)
# This will show which docker-compose file each container belongs to
print(run_step("docker ps --format 'table {{.Names}}\t{{.Label \"com.docker.compose.project.working_dir\"}}'"))

print("\n" + "=" * 60)
print("CHECKING ALL FOLDERS IN /home/ubuntu")
print("=" * 60)
print(run_step("ls -F /home/ubuntu"))

print("\n" + "=" * 60)
print("CHECKING FOR ANY RECENTLY MODIFIED FILES")
print("=" * 60)
print(run_step("find /home/ubuntu -mmin -120 -type f | head -n 20"))
