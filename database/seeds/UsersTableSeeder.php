<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     *
     * Insert demo data Users table
     */
    public function run()
    {
        // install for customer
        $first_name = "Sab Sung Dai";// session('first_name');
        $last_name = "Sab Sung Dai"; // session('last_name');
        $email = "sabsungdai_admin@gmail.com"; // session('email');
        $password = "sabsungdai_admin123";// session('password');

        DB::table('users')->insert([

            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => Hash::make($password),
            'verified' => 1,
            'is_admin' => 1,
            'user_type' => 'staff',
            'branch_id' => 1,
            'token' => ''
        ]);
    }
}
