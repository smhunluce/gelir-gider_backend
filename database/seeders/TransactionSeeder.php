<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (!$category_user = DB::table('transaction_categories')->pluck('user_id', 'id'))
            return;

        $category_user = $category_user->toArray();
        $currencies    = ['TRY', 'USD', 'EUR'];

        $faker = Factory::create();
        if (!$date = DB::table('transactions')->max('created_at'))
            $date = $faker->dateTimeBetween('-3 years')->format('Y-m-d H:i:s');

        for ($i = 0; $i < 100; $i++) {

            $category_id = array_rand($category_user);
            $user_id     = $category_user[$category_id];
            $date        = date('Y-m-d H:i:s', strtotime($date) + (60 * rand(1440, 2880)));

            DB::table('transactions')->insert([
                'user_id'          => $user_id,
                'category_id'      => $category_id,
                'amount'           => rand(500, 50000) / 100,
                'currency'         => $currencies[array_rand($currencies)],
                'transaction_date' => $date,
                'description'      => Str::random(rand(20, 40)),
                'created_at'       => $date,
                'updated_at'       => $date,
            ]);

        }
    }
}
