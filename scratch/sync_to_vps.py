import os
import pty
import time
import sys

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

files_to_sync = [
    'app/Http/Controllers/Desa/AdministrasiController.php',
    'app/Http/Controllers/Kecamatan/PemerintahanController.php',
    'app/Http/Controllers/ApplicationProfileController.php',
    'app/Models/User.php',
    'app/Models/PersonilDesa.php',
    'app/Models/Menu.php',
    'app/Services/WhatsApp/AiHandler.php',
    'resources/views/desa/administrasi/personil/create.blade.php',
    'resources/views/desa/administrasi/personil/edit.blade.php',
    'resources/views/desa/administrasi/personil/index.blade.php',
    'resources/views/kecamatan/pemerintahan/personil/index.blade.php',
    'resources/views/kecamatan/settings/features.blade.php',
    'resources/views/layouts/partials/sidebar.blade.php',
    'routes/desa.php',
    'routes/kecamatan.php'
]

def sync_files():
    for f in files_to_sync:
        local_path = f"app/{f}"
        remote_path = f"{user}@{host}:~/kecamatanSAE/app/{f}"
        
        print(f"Syncing {f}...")
        
        pid, fd = pty.fork()
        if pid == 0:
            os.execv('/usr/bin/scp', ['scp', '-o', 'StrictHostKeyChecking=no', local_path, remote_path])
        else:
            # Wait for password prompt
            start = time.time()
            while time.time() - start < 30:
                try:
                    data = os.read(fd, 4096)
                    if b"password:" in data:
                        os.write(fd, (pw + "\n").encode())
                        break
                    if not data: break
                except:
                    break
            
            # Wait for completion
            os.waitpid(pid, 0)
            print(f"Finished {f}")

if __name__ == "__main__":
    sync_files()
    
    # Clear cache on remote
    print("\nCleaning cache on VPS...")
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', 'cd kecamatanSAE && sudo docker compose exec -T app php artisan view:clear && sudo docker compose exec -T app php artisan cache:clear && sudo docker compose exec -T app php artisan config:clear'])
    else:
        time.sleep(2); os.write(fd, (pw + "\n").encode()); time.sleep(2); os.write(fd, (pw + "\n").encode())
        os.waitpid(pid, 0)
        print("Cache cleared!")
