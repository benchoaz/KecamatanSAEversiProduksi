import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'
app_path = '/home/ubuntu/kecamatanSAE/app'
new_url = 'https://waha-ohiqijryhkzp.boba.sumopod.my.id'
new_key = 'tq4ZqP4Ck40WENkfk5Aah4tCF4u5DBtg'
new_session = 'session_01kqepctxvfe6jx212f0b4h48t'

def run_ssh_step(cmd):
    print(f"🚀 Running: {cmd}")
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < 120:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                text = chunk.decode(errors='ignore')
                print(text, end='', flush=True)
                
                if "password:" in text.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return output.decode(errors='ignore')

print("--- FINAL SYNC: URL, KEY, SESSION ON VPS ---")

# Update URL, Key, and Session in .env
update_env_cmd = (
    f"cd {app_path} && "
    f"sed -i 's|WAHA_API_URL=.*|WAHA_API_URL={new_url}|' .env && "
    f"sed -i 's|WAHA_API_KEY=.*|WAHA_API_KEY={new_key}|' .env && "
    f"sed -i 's|WAHA_SESSION=.*|WAHA_SESSION={new_session}|' .env"
)
run_ssh_step(update_env_cmd)

# Restart Container
restart_cmd = f"cd {app_path} && docker compose restart app"
run_ssh_step(restart_cmd)

print("\n✅ VPS SYNC COMPLETED!")
