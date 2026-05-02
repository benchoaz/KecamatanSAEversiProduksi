cd ~/kecamatanSAE
echo "\$u = App\Models\User::where('username', 'admin')->first() ?: new App\Models\User(); \$u->username = 'admin'; \$u->password = Hash::make('admin123'); \$u->role_id = 1; \$u->status = 'aktif'; \$u->nama_lengkap = 'Administrator Pusat'; \$u->save(); \$u->assignRole('super_admin_kabupaten'); echo 'DONE';" > update_admin_tinker.php
sudo docker compose -f docker-compose.vps.yml exec -T app php artisan tinker < update_admin_tinker.php
rm update_admin_tinker.php
