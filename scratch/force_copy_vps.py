import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_step(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        start = time.time()
        while time.time() - start < 15:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

# Cat host file to check it
print("HOST AppProfile.php fillable:")
print(run_step("sudo -S cat /home/ubuntu/kecamatanSAE/app/app/Models/AppProfile.php | grep -i openai"))

print("\nCopying files directly into docker container...")
run_step("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml cp /home/ubuntu/kecamatanSAE/app/app/Models/AppProfile.php app:/var/www/app/Models/AppProfile.php")
run_step("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml cp /home/ubuntu/kecamatanSAE/app/database/migrations/2026_05_03_063302_add_ai_api_keys_to_app_profiles_table.php app:/var/www/database/migrations/")

print("\nRunning migrate in container:")
print(run_step("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan migrate --force"))

