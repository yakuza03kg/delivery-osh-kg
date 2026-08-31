<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'courier@example.com'],
            [
                'name' => 'Азамат',
                'password' => Hash::make('password'),
                'role' => 'courier',
            ],
        );

        Branch::query()->updateOrCreate(
            ['name' => 'Барбол — Центр'],
            [
                'address' => 'Бишкек, ул. Ленина, 100',
                'latitude' => 42.8746,
                'longitude' => 74.5698,
                'is_active' => true,
            ],
        );

        Branch::query()->updateOrCreate(
            ['name' => 'Барбол — Запад'],
            [
                'address' => 'Бишкек, ул. Курманжан Датка, 50',
                'latitude' => 42.8667,
                'longitude' => 74.5564,
                'is_active' => true,
            ],
        );

        $tariff = Tariff::query()->updateOrCreate(
            ['name' => 'Стандартный тариф'],
            [
                'mode' => Tariff::MODE_PER_KM,
                'price_per_km' => 100,
                'base_km' => 0,
                'base_price' => 0,
                'additional_price_per_km' => null,
                'max_price' => null,
                'rounding' => 'none',
                'is_active' => true,
            ],
        );

        Tariff::query()->where('id', '!=', $tariff->id)->update(['is_active' => false]);
    }
}
