<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory;


class TransactionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        if (!$date = DB::table('transaction_categories')->max('created_at'))
            $date = $faker->dateTimeBetween('-3 years')->format('Y-m-d H:i:s');

        $j = rand(20, 30);
        for ($i = 0; $i < $j; $i++) {

            $date = date('Y-m-d H:i:s', strtotime($date) + (60 * rand(1440, 2880)));
            DB::table('transaction_categories')->insert([
                'user_id'    => rand(1, 2),
                'name'       => Str::random(10),
                'type'       => rand(0, 1) ? 'income' : 'expense',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

        }
    }
}
