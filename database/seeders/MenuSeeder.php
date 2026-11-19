<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
      
        User::updateOrCreate(
            ['email' => 'admin@naspad.com'],
            [
                'name' => 'Admin Naspad',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

   
        User::updateOrCreate(
            ['email' => 'user@naspad.com'],
            [
                'name' => 'Pembeli Naspad',
                'role' => 'user',
                'password' => Hash::make('password'),
            ]
        );

      
        $menus = [
            ['nama_lauk' => 'Rendang Sapi', 'kategori' => 'Daging', 'harga' => 22000, 'stok' => 20],
            ['nama_lauk' => 'Dendeng Balado', 'kategori' => 'Daging', 'harga' => 20000, 'stok' => 15],
            ['nama_lauk' => 'Ayam Pop', 'kategori' => 'Ayam', 'harga' => 18000, 'stok' => 25],
            ['nama_lauk' => 'Ayam Bakar Padang', 'kategori' => 'Ayam', 'harga' => 18000, 'stok' => 20],
            ['nama_lauk' => 'Gulai Tunjang', 'kategori' => 'Daging', 'harga' => 21000, 'stok' => 12],
            ['nama_lauk' => 'Sayur Nangka & Sambal Ijo', 'kategori' => 'Sayur & Sambal', 'harga' => 5000, 'stok' => 50],
        ];

        foreach ($menus as $menu) {
            Menu::firstOrCreate(
                ['nama_lauk' => $menu['nama_lauk']],
                $menu
            );
        }
    }
}