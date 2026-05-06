import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_ssh(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        time.sleep(2)
        os.write(fd, (pw + "\n").encode())
        time.sleep(1)
        os.write(fd, (pw + "\n").encode())
        os.waitpid(pid, 0)

def sync_compose():
    print("Syncing docker-compose.yml...")
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/scp', ['scp', '-o', 'StrictHostKeyChecking=no', 'docker-compose.yml', f'{user}@{host}:~/kecamatanSAE/docker-compose.yml'])
    else:
        time.sleep(2)
        os.write(fd, (pw + "\n").encode())
        os.waitpid(pid, 0)
    
    print("Restarting containers with new volumes...")
    run_ssh("cd kecamatanSAE && sudo docker compose up -d")

if __name__ == "__main__":
    sync_compose()
    
    # Wait and then check container status
    import time
    time.sleep(5)
    
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', 'cd kecamatanSAE && sudo docker compose up -d --remove-orphans'])
    else:
        time.sleep(2)
        os.write(fd, (pw + "\n").encode())
        time.sleep(2)
        os.write(fd, (pw + "\n").encode())
        output = b""
        start = time.time()
        while time.time() - start < 30:
            try:
                data = os.read(fd, 4096)
                if not data: break
                output += data
            except:
                break
        os.waitpid(pid, 0)
        print(output.decode(errors='ignore'))
