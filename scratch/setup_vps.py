import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_remote_interactive(cmd_list):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', 'bash'])
    else:
        time.sleep(2)
        os.write(fd, b'nebula-57@-ocean\n') # SSH Password
        time.sleep(2)
        
        for cmd in cmd_list:
            print(f"Executing: {cmd}")
            os.write(fd, (cmd + "\n").encode())
            # Wait for command to finish or periodic output
            start = time.time()
            while time.time() - start < 120: # Timeout per command
                try:
                    data = os.read(fd, 4096)
                    if not data: break
                    decoded = data.decode(errors='ignore')
                    print(decoded, end='', flush=True)
                    if b"[sudo] password for ubuntu:" in data:
                        os.write(fd, (pw + "\n").encode())
                    if b"ubuntu@": # Prompt returned
                         # Check if it's the real prompt and not just text
                         if decoded.strip().endswith("$") or decoded.strip().endswith("#"):
                             break
                except:
                    break
        
        os.write(fd, b"exit\n")

# Commands to setup the VPS
setup_cmds = [
    "sudo apt-get update",
    "sudo apt-get install -y curl git",
    "curl -fsSL https://get.docker.com -o get-docker.sh && sudo sh get-docker.sh",
    "sudo usermod -aG docker ubuntu",
    "git clone https://github.com/benchoaz/KecamatanSAEversiProduksi.git kecamatanSAE",
    "cd kecamatanSAE && cp app/.env.example app/.env"
]

run_remote_interactive(setup_cmds)
