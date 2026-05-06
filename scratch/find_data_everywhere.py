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
print("LISTING ALL DATABASES IN POSTGRES")
print("=" * 60)
db_list_cmd = """
cd kecamatanSAE && docker compose exec -T db psql -U user -l
"""
print(run_step(db_list_cmd))

print("\n" + "=" * 60)
print("CHECKING ALL VOLUMES SIZE")
print("=" * 60)
size_cmd = """
sudo du -sh /var/lib/docker/volumes/* | grep pgdata
"""
# Need sudo for /var/lib/docker/volumes/
# I'll try to check the sizes of volumes by running a temp container that mounts all of them
size_cmd_docker = """
docker run --rm -v /var/lib/docker/volumes:/volumes alpine sh -c 'du -sh /volumes/*pgdata*'
"""
print(run_step(size_cmd_docker))

print("\n" + "=" * 60)
print("CHECKING FOR DUMP FILES")
print("=" * 60)
print(run_step("find /home/ubuntu -name '*.sql' -o -name '*.dump'"))
