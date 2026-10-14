<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Akun demo untuk testing. Aman dijalankan ulang.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Admin Toko HP', 'email' => 'admin@tokohp.local', 'password' => 'admin123', 'role' => User::ROLE_ADMIN],
            ['name' => 'Rizky', 'email' => 'rizky@tokohp.local', 'password' => 'karyawan123', 'role' => User::ROLE_KARYAWAN],
            ['name' => 'Dimas', 'email' => 'dimas@tokohp.local', 'password' => 'karyawan123', 'role' => User::ROLE_KARYAWAN],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => $account['password'], 'role' => $account['role']]
            );

            if ($user->wasRecentlyCreated) {
                $this->command?->info("Akun demo dibuat: {$account['email']}");
            }
        }
    }
}
