import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_scp(local, remote):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/scp', ['scp', '-o', 'StrictHostKeyChecking=no', local, f'{user}@{host}:{remote}'])
    else:
        time.sleep(2)
        os.write(fd, (pw + "\n").encode())
        output = b""
        start = time.time()
        while time.time() - start < 10:
            try:
                data = os.read(fd, 4096)
                if not data: break
                output += data
            except:
                break
        os.waitpid(pid, 0)
        return output.decode(errors='ignore')

if __name__ == "__main__":
    print(run_scp("app/routes/kecamatan.php", "~/kecamatanSAE/app/routes/kecamatan.php"))
    print(run_scp("app/app/Http/Controllers/Kecamatan/WahaN8nController.php", "~/kecamatanSAE/app/app/Http/Controllers/Kecamatan/WahaN8nController.php"))
    print(run_scp("app/app/Models/AppProfile.php", "~/kecamatanSAE/app/app/Models/AppProfile.php"))
