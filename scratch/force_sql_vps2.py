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

print("Executing RAW SQL...")
sql_query = "ALTER TABLE app_profiles ADD COLUMN IF NOT EXISTS ai_provider VARCHAR(255) DEFAULT \\'gemini\\', ADD COLUMN IF NOT EXISTS openai_api_key TEXT, ADD COLUMN IF NOT EXISTS google_api_key TEXT, ADD COLUMN IF NOT EXISTS anthropic_api_key TEXT, ADD COLUMN IF NOT EXISTS xai_api_key TEXT, ADD COLUMN IF NOT EXISTS deepseek_api_key TEXT, ADD COLUMN IF NOT EXISTS dashscope_api_key TEXT, ADD COLUMN IF NOT EXISTS zhipu_api_key TEXT, ADD COLUMN IF NOT EXISTS openrouter_api_key TEXT, ADD COLUMN IF NOT EXISTS alpha_vantage_api_key TEXT;"
print(run_step(f"sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T db psql -U user -d dashboard_kecamatan -c \"{sql_query}\""))

