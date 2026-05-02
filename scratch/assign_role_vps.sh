cd ~/kecamatanSAE
echo "Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin_kabupaten', 'guard_name' => 'web']); \$u = App\Models\User::where('username', 'admin')->first(); \$u->assignRole('super_admin_kabupaten'); echo 'DONE';" > assign_role.php
sudo docker compose -f docker-compose.vps.yml exec -T app php artisan tinker < assign_role.php
rm assign_role.php
