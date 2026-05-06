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
print("CHECKING GIT LOG FOR DOCKER-COMPOSE ON VPS")
print("=" * 60)
print(run_step("cd kecamatanSAE && git log -p -n 5 docker-compose.yml"))

print("\n" + "=" * 60)
print("CHECKING ALL DOCKER VOLUMES WITH INSPECT")
print("=" * 60)
# List volumes and their mount dates if possible
print(run_step("docker volume ls --format '{{.Name}}' | xargs -I {} docker volume inspect {}"))

print("\n" + "=" * 60)
print("CHECKING FOR OTHER PROJECT DIRECTORIES")
print("=" * 60)
print(run_step("ls -la /home/ubuntu"))
