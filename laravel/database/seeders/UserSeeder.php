<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        User::factory()
        ->count(6)
        ->state(new Sequence(
            ['email_verified_at' => null],
            ['email_verified_at' => now()],
        ))
        ->create();
    }
}
