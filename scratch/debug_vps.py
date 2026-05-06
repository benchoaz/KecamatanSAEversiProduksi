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
        # Wait for password prompt
        time.sleep(2)
        os.write(fd, (pw + "\n").encode())
        time.sleep(1)
        os.write(fd, (pw + "\n").encode()) # for sudo if needed
        
        output = b""
        start = time.time()
        while time.time() - start < 15:
            try:
                data = os.read(fd, 4096)
                if not data: break
                output += data
            except:
                break
        os.waitpid(pid, 0)
        return output.decode(errors='ignore')

if __name__ == "__main__":
    # Sync updated AiHandler.php to VPS
    from manual_sync import run_scp
    print(run_scp("app/app/Services/WhatsApp/AiHandler.php", "~/kecamatanSAE/app/app/Services/WhatsApp/AiHandler.php"))
    
    # Clear cache so new code is used
    print(run_ssh("cd kecamatanSAE && sudo docker compose exec -T app php artisan cache:clear && sudo docker compose exec -T app php artisan view:clear"))
