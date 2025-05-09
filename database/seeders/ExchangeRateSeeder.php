<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['base' => 'USD'],
            ['base' => 'AUD'],
            ['base' => 'CAD'],
            ['base' => 'GBP'],
        ];

        foreach ($currencies as $currency) {
            for ($i = 90; $i >= 0; $i--) {
                ExchangeRate::create([
                    'base_currency' => $currency['base'],
                    'rate' => rand(100, 400) + (rand(0, 9999) / 10000),
                    'date' => Carbon::today()->subDays($i)->toDateString(),
                ]);
            }
        }
    }
}
