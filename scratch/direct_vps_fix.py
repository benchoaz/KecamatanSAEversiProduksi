import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_ssh_cmd(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        start = time.time()
        while time.time() - start < 60:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

print("--- DIRECT VPS FIX ---")

# 1. Update Layout to use non-minified CSS and add transparency style
# We use sed to replace the line
print("Updating Layout...")
cmd_update_layout = (
    "sudo sed -i 's/css\\/min\\/dashboard.min.css/css\\/dashboard.css/g' "
    "/home/ubuntu/kecamatanSAE/app/resources/views/layouts/kecamatan.blade.php"
)
run_ssh_cmd(cmd_update_layout)

# 2. Update dashboard.css directly on VPS host
print("Updating CSS...")
# Remove background from .logo-icon in dashboard.css
cmd_update_css = (
    "sudo sed -i '/.logo-icon {/,/}/ s/background: linear-gradient[^;]*;/background: transparent !important;/' "
    "/home/ubuntu/kecamatanSAE/app/public/css/dashboard.css && "
    "sudo sed -i '/.logo-icon {/,/}/ s/box-shadow:[^;]*;/box-shadow: none !important;/' "
    "/home/ubuntu/kecamatanSAE/app/public/css/dashboard.css"
)
run_ssh_cmd(cmd_update_css)

# 3. Fix Storage Link inside container
print("Fixing Storage Link...")
run_ssh_cmd("sudo docker exec kecamatan-app rm -f /var/www/public/storage")
run_ssh_cmd("sudo docker exec kecamatan-app ln -snf ../storage/app/public /var/www/public/storage")

# 4. Permissions inside container
print("Fixing Permissions...")
run_ssh_cmd("sudo docker exec kecamatan-app chown -R www-data:www-data /var/www/storage /var/www/public")
run_ssh_cmd("sudo docker exec kecamatan-app chmod -R 775 /var/www/storage /var/www/public")

# 5. Clear Caches
print("Clearing Caches...")
run_ssh_cmd("sudo docker exec kecamatan-app php artisan optimize:clear")
run_ssh_cmd("sudo docker exec kecamatan-app php artisan view:clear")

print("--- DONE ---")
