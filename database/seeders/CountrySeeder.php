<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usaStates = [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
        ];

        DB::table('countries')->upsert([
            ['code' => 'geo', 'name' => 'Georgia', 'states' => null],
            ['code' => 'ind', 'name' => 'India', 'states' => null],
            [
                'code' => 'usa',
                'name' => 'United States of America',
                'states' => json_encode($usaStates, JSON_THROW_ON_ERROR),
            ],
            ['code' => 'ger', 'name' => 'Germany', 'states' => null],
        ], ['code'], ['name', 'states']);
    }
}
