<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Buat akun admin pertama. Kredensial dari .env:
     *   ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
     * Akun yang sudah ada dilewati, jadi seeder aman dijalankan ulang.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');

        if (! $email) {
            $this->command?->warn('ADMIN_EMAIL belum diisi, lewati.');

            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Akun {$email} sudah ada, lewati.");

            return;
        }

        User::create([
            'name' => env('ADMIN_NAME', 'Admin'),
            'email' => $email,
            'password' => env('ADMIN_PASSWORD', 'password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->command?->info("Admin dibuat: {$email}");
    }
}