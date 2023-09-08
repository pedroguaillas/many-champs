<?php

namespace Database\Seeders;

use App\Models\Progress;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('progress')->insert(['description' => 'Fase de grupos']);
        DB::table('progress')->insert(['description' => 'Octavos de final']);
        DB::table('progress')->insert(['description' => 'Cuartos de final']);
        DB::table('progress')->insert(['description' => 'Semi final']);
        DB::table('progress')->insert(['description' => 'Final']);
    }
}
