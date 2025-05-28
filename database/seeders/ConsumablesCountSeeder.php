<?php

namespace Database\Seeders;

use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use App\Models\Consumable\ConsumableCountInstalled;
use Illuminate\Support\Facades\DB;

/**
 * Создание тестовых пользователей
 */
class ConsumablesCountSeeder extends AbstractSeeder
{   

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgCodes = DB::table('organizations')->pluck('code')->toArray();
        
        foreach($orgCodes as $code) {

            $countConsumables = rand(5,10);

            for ($i=0; $i<$countConsumables; $i++) {
                
                $consumableId = $this->getRandomConsumableId();

                $countConsumables = rand(5, 10);

                // create consumable count
                $consumableCountId = DB::table('consumables_counts')->insertGetId([
                    'id_consumable' => $consumableId,
                    'count' => $countConsumables,
                ]);

                // create consumable count organization
                DB::table('consumables_counts_organizations')
                    ->insert([
                        'id_consumable_count' => $consumableCountId,
                        'org_code' => $code,
                    ]);
                

                // create installed and added
                for($x=0; $x<rand(1, 3); $x++) {
                    $date = fake()->dateTimeThisYear(date('Y-12-31'));
                    DB::table('consumables_counts_added')->insert([
                        'id_consumable_count' => $consumableCountId,
                        'id_author' => $this->getRandomAuthorId(),
                        'count' => rand(1, 3),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);                    
                }

                for($x=0; $x<rand(1, 4); $x++) {
                    $date = fake()->dateTimeThisYear(date('Y-12-31'));
                    DB::table('consumables_counts_installed')->insert([
                        'id_consumable_count' => $consumableCountId,
                        'id_printer_workplace' => $this->getRandomPrinterWorkplaceId($code),
                        'id_author' => $this->getRandomAuthorId(),
                        'count' => rand(1, 2),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);                    
                }
            }
        }        
    }

    

    private function getRandomConsumableId()
    {
        $consumable = DB::table('consumables')->orderByRaw('gen_random_uuid()')->first();
        return $consumable->id;
    }

    private function getRandomAuthorId()
    {
        $author = DB::table('users')->orderByRaw('gen_random_uuid()')->first();
        return $author->id;
    }

    private function getRandomPrinterWorkplaceId(string $orgCode)
    {
        $printerWorkplace = DB::table('printers_workplace')
            ->where('org_code', $orgCode)
            ->orderByRaw('gen_random_uuid()')
            ->first();
        return $printerWorkplace->id;
    }


}
