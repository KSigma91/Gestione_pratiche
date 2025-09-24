<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Controlla se esiste già un admin con email admin@local
        $exists = DB::table('users')->where('email', 'admin@local')->first();
        if ($exists) {
            $this->command->info('Admin esistente, saltando seeder.');
            return;
        }

        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'amglab.info@gmail.com',
            'password' => Hash::make('amglab'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $this->command->info('Admin creato: admin@local / password');
    }
}
