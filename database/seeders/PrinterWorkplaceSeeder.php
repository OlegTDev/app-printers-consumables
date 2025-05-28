<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrinterWorkplaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $orgCodes = DB::table('organizations')->pluck('code')->toArray();

        foreach ($orgCodes as $code) {
            for ($i = 0; $i <= rand(10, 15); $i++) {
                $date = fake()->dateTimeThisYear();
                DB::table('printers_workplace')->insert([
                    'id_printer' => $this->getRandomPrinterId(),
                    'id_author' => $this->getRandomAuthorId(),
                    'org_code' => $code,
                    'location' => fake()->numberBetween(100, 700),
                    'serial_number' => fake()->regexify('[A-Z0-9]{10}'),
                    'inventory_number' => fake()->regexify('[0-9]{10}'),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
    }

    private function getRandomPrinterId()
    {
        $printer = DB::table('printers')->orderByRaw('gen_random_uuid()')->first();
        return $printer->id;
    }

    private function getRandomAuthorId()
    {
        $author = DB::table('users')->orderByRaw('gen_random_uuid()')->first();
        return $author->id;
    }

}
