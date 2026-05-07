import os
import subprocess
import sys

def run_command(command, use_sudo=False):
    if use_sudo:
        command = f"sudo {command}"
    print(f"Executing: {command}")
    try:
        result = subprocess.run(command, shell=True, check=True, text=True, capture_output=True)
        print(result.stdout)
        return True
    except subprocess.CalledProcessError as e:
        print(f"Error: {e.stderr}")
        return False

def main():
    print("=== KecamatanSAE VPS Automated Fixer ===")
    
    # 1. Fix Permissions
    print("\n1. Fixing permissions for 'app/storage'...")
    if not run_command("chown -R ubuntu:ubuntu app/storage", use_sudo=True):
        print("Failed to fix permissions. Proceeding anyway...")

    # 2. Reset Git
    print("\n2. Resetting local changes to match GitHub...")
    if not run_command("git reset --hard origin/main"):
        print("Failed to reset git. Trying to fetch first...")
        run_command("git fetch origin")
        if not run_command("git reset --hard origin/main"):
            print("CRITICAL ERROR: Could not reset git.")
            sys.exit(1)

    # 3. Pull latest changes
    print("\n3. Pulling latest code...")
    run_command("git pull origin main")

    # 4. Rebuild Containers
    print("\n4. Rebuilding Docker containers...")
    run_command("docker compose -f docker-compose.vps.yml up -d --build")

    # 5. Clear Cache
    print("\n5. Clearing Laravel cache...")
    run_command("docker exec kecamatan-app php artisan optimize:clear")

    print("\n=== All Done! Please check your website. ===")

if __name__ == "__main__":
    main()
