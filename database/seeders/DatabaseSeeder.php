<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Image;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
       $user = User::factory(20)->create()->each(function ($user) {
        Image::factory()->create([
            'imageable_id' => $user->id,
            'imageable_type' => User::class,
            ]);
        });
    }

}