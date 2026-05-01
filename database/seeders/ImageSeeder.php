<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImageSeeder extends Seeder
{
    public function run()
    {
        DB::table('images')->insert([
            [
                'owner_id' => 1,
                'filename' => 'sample01.jpg',
                'title' => null
            ],
            [
                'owner_id' => 1,
                'filename' => 'sample02.jpg',
                'title' => null
            ],
            [
                'owner_id' => 1,
                'filename' => 'sample03.jpg',
                'title' => null
            ],
            [
                'owner_id' => 1,
                'filename' => 'sample04.jpg',
                'title' => null
            ],
            [
                'owner_id' => 2,
                'filename' => 'sample05.jpg',
                'title' => null
            ],
            [
                'owner_id' => 2,
                'filename' => 'sample06.jpg',
                'title' => null
            ],
            [
                'owner_id' => 2,
                'filename' => 'sample07.jpg',
                'title' => null
            ],
            [
                'owner_id' => 2,
                'filename' => 'sample08.jpg',
                'title' => null
            ],
            [
                'owner_id' => 3,
                'filename' => 'sample09.jpg',
                'title' => null
            ],
            [
                'owner_id' => 3,
                'filename' => 'sample10.jpg',
                'title' => null
            ],
            [
                'owner_id' => 3,
                'filename' => 'sample11.jpg',
                'title' => null
            ],
            [
                'owner_id' => 3,
                'filename' => 'sample12.jpg',
                'title' => null
            ],
            [
                'owner_id' => 4,
                'filename' => 'sample13.jpg',
                'title' => null
            ],
            [
                'owner_id' => 4,
                'filename' => 'sample14.jpg',
                'title' => null
            ],
            [
                'owner_id' => 4,
                'filename' => 'sample15.jpg',
                'title' => null
            ],
            [
                'owner_id' => 4,
                'filename' => 'sample16.jpg',
                'title' => null
            ],
            [
                'owner_id' => 5,
                'filename' => 'sample17.jpg',
                'title' => null
            ],
            [
                'owner_id' => 5,
                'filename' => 'sample18.jpg',
                'title' => null
            ],
            [
                'owner_id' => 5,
                'filename' => 'sample19.jpg',
                'title' => null
            ],
            [
                'owner_id' => 5,
                'filename' => 'sample20.jpg',
                'title' => null
            ],
            [
                'owner_id' => 6,
                'filename' => 'sample21.jpg',
                'title' => null
            ],
            [
                'owner_id' => 6,
                'filename' => 'sample22.jpg',
                'title' => null
            ],
            [
                'owner_id' => 6,
                'filename' => 'sample23.jpg',
                'title' => null
            ],
            [
                'owner_id' => 6,
                'filename' => 'sample24.jpg',
                'title' => null
            ],
        ]);
    }
}
