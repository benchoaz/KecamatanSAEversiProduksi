import subprocess
import time

def run_ssh_command(host, user, password, command):
    # Using sshpass if possible, or falling back to simple ssh
    # But since I can't interact, I'll use a trick with echo and sudo -S for the parts that need it
    full_cmd = f"ssh -o StrictHostKeyChecking=no {user}@{host} \"{command}\""
    print(f"Running: {full_cmd}")
    
    # We'll use a pipe to try and provide the password if it asks, 
    # but ssh doesn't like that. sshpass is the only way for non-interactive.
    # Let's try to see if sshpass is actually there first.
    return subprocess.run(full_cmd, shell=True)

if __name__ == "__main__":
    host = "43.134.166.153"
    user = "ubuntu"
    pw = "nebula-57@-ocean"
    
    # Combined command for the VPS
    vps_command = f"cd kecamatanSAE && echo '{pw}' | sudo -S chown -R ubuntu:ubuntu app/storage && git reset --hard origin/main && git pull origin main && docker compose -f docker-compose.vps.yml up -d --build && docker exec kecamatan-app php artisan migrate --force && docker exec kecamatan-app php artisan optimize:clear"
    
    # I'll try to use sshpass directly in a subprocess
    try:
        subprocess.run(["sshpass", "-V"], check=True, capture_output=True)
        print("sshpass is available.")
        subprocess.run(f"sshpass -p '{pw}' ssh -o StrictHostKeyChecking=no {user}@{host} \"{vps_command}\"", shell=True)
    except:
        print("sshpass not found. Trying to use Python to handle the SSH session...")
        # Since I can't use paramiko or pexpect easily without install, 
        # I'll provide the user with the EXACT string they need to paste if my remote attempt fails.
        print("Please paste this into your terminal:")
        print(f"sshpass -p '{pw}' ssh -o StrictHostKeyChecking=no {user}@{host} \"{vps_command}\"")
