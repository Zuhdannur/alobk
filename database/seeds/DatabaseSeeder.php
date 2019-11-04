<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('SekolahTableSeeder');
<<<<<<< HEAD
        $this->call('UsersTableSeeder');
=======
//        $this->call('UsersTableSeeder');
>>>>>>> 9aebd26a20abbaca68b40e8891f5f7bbbe65eb14
    }
}
