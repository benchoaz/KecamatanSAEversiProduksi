
import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_remote_command(command):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', command])
    else:
        output = b""
        start_time = time.time()
        while time.time() - start_time < 10:
            try:
                data = os.read(fd, 1024)
                output += data
                if b"password:" in data.lower():
                    os.write(fd, (pw + '\n').encode())
                    break
            except OSError:
                break
        
        result = b""
        start_time = time.time()
        while time.time() - start_time < 20:
            try:
                data = os.read(fd, 1024)
                if not data:
                    break
                result += data
            except OSError:
                break
        
        print(result.decode(errors='ignore'))

# Check landing.blade.php around the branding section
run_remote_command("cd kecamatanSAE && grep -A 50 'branding_image_path' app/resources/views/landing.blade.php")
