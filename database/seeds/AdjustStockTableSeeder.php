<?php

use Illuminate\Database\Seeder;

class AdjustStockTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('adjust_product_stock_types')->insert([
            [
                'title' => 'Damaged Product',
                'created_by' => 1,
                'created_at' => now()
            ],
        ]);
    }
}
