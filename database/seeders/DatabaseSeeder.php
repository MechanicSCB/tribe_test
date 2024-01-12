<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Member;
use App\Models\Result;
use Faker\Factory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Factory::create();
        $members = [];

        for($i=1;$i<=500;$i++){
            $members[] = [
                'id' => $i,
                'email' => $faker->unique()->email
            ];
        }

        Member::query()->upsert($members, ['id']);

        $results = [];

        for($i=1;$i<=10000;$i++){
            $results[] = [
                'id' => $i,
                // пусть результаты будут только у игроков с четным id, чтобы были игроки без результатов
                'member_id' => rand(0,5) ? rand(1,250)*2 : null,
                'milliseconds' => rand(1000, 50000),
            ];
        }

        Result::query()->upsert($results, ['id']);
    }
}
