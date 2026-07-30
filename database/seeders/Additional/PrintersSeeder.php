<?php


namespace Database\Seeders\Additional;

use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Services\Consumables\ConsumableCountService;
use App\Models\Consumable\Consumable;
use App\Models\Printer\Printer;
use Database\Seeders\Concerns\HasRegionCode;
use Database\Seeders\Concerns\HasUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrintersSeeder extends Seeder
{
    use HasUser, HasRegionCode;

    private int $inventoryNumber = 1;

    public function __construct(private readonly ConsumableCountService $consumableCountService)
    {}

    public function run(): void
    {
        $author = $this->getUserAdmin();
        $randomUser = $this->getRandomUser();
        $printers = (require database_path('seeders/Additional/data.php'))['printers'];
        $organizations = (require database_path('seeders/Additional/data.php'))['organizations'];

        foreach ($printers as $printer) {
            // Printers
            $printerModel = Printer::updateOrCreate(
                ['vendor' => $printer['vendor'], 'model' => $printer['model']],
                ['is_color_print' => $printer['is_color_print'], 'id_author' => $author->id],
            );

            // Printers Workplaces
            $workplacesData = array_map(fn ($wp) => [
                ...$wp,
                'inventory_number' => \sprintf('1%09s', ++$this->inventoryNumber),
                'id_author' => $randomUser->id,
            ], $printer['workplaces']);
            $workplacesModels = $printerModel->printersWorkplaces()->createMany($workplacesData);

            // Consumables
            $syncData = [];
            foreach ($printer['consumables'] as $consumableData) {
                ['type' => $type, 'name' => $name] = $consumableData;
                unset($consumableData['type'], $consumableData['name']);

                $consumable = Consumable::updateOrCreate(
                    ['type' => $type, 'name' => $name],
                    [...$consumableData, 'id_author' => $author->id],
                );

                $syncData[$consumable->id] = [
                    'id_author' => $author->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $orgCodes = array_keys($organizations);


                // Consumable Count
                $dateCreate = now()->subMonths(2);
                foreach ($organizations as $code => $organizationData) {
                    $consumableCount = ConsumableCount::create([
                        'id_consumable' => $consumable->id,
                        'count' => random_int(1, 30),
                        'created_at' => $dateCreate,
                        'updated_at' => $dateCreate,
                    ]);

                    DB::table('consumables_counts_organizations')->insert([
                        'id_consumable_count' => $consumableCount->id,
                        'org_code' => $code,
                    ]);

                    // Consumable Count Added
                    for ($i=0; $i<=random_int(2, 5); $i++) {
                        $date = now()->subDays(random_int(1, 55));
                        $user = $this->getRandomUser();

                        ConsumableCountAdded::create([
                            'id_consumable_count' => $consumableCount->id,
                            'id_author' => $user->id,
                            'count' => random_int(2, 10),
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);
                    }

                    // Consumable Count Installed
                    for ($i=0; $i<=random_int(2, 10); $i++) {
                        $date = now()->subDays(random_int(1, 55));
                        $user = $this->getRandomUser();
                        $ws = $workplacesModels->firstWhere('org_code', $code);

                        ConsumableCountInstalled::create([
                            'id_consumable_count' => $consumableCount->id,
                            'id_printer_workplace' => $ws->id,
                            'id_author' => $user->id,
                            'count' => random_int(1, 3),
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);
                    }
                }
            }

            $printerModel->consumables()->syncWithoutDetaching($syncData);
        }
    }


}
