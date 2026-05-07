import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_remote(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        full_output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < 30:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                full_output += chunk
                if b"password:" in chunk.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return full_output.decode(errors='ignore')

tinker_cmd = 'docker exec kecamatan-app php artisan tinker --execute="echo \\"VPS WAHA SETTING:\\"; print_r(\\\\App\\\\Models\\\\WahaN8nSetting::first()->only([\'bot_number\', \'operator_number\'])); echo \\"VPS PROFILE:\\"; print_r(\\\\App\\\\Models\\\\AppProfile::first()->only([\'whatsapp_complaint\', \'whatsapp_bot_number\']));"'
print(run_remote(tinker_cmd))
