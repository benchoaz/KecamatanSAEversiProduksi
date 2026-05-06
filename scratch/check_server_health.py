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
print("DOCKER PS STATUS")
print("=" * 60)
print(run_step("docker ps"))

print("\n" + "=" * 60)
print("TRAEFIK LOGS (LAST 20 LINES)")
print("=" * 60)
print(run_step("docker logs --tail 20 traefik-gateway"))

print("\n" + "=" * 60)
print("NGINX LOGS (LAST 20 LINES)")
print("=" * 60)
print(run_step("docker logs --tail 20 kecamatan-nginx"))
