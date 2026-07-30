<?php

namespace Database\Seeders\Additional;

use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderConsumableDetails;
use App\Models\Order\OrderMiscDetails;
use App\Models\Order\OrderSparePartDetails;
use App\Models\Order\OrderStatusEnum;
use App\Models\Printer\PrinterWorkplace;
use Carbon\CarbonInterface;
use Database\Seeders\Concerns\HasUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class OrdersSeeder extends Seeder
{
    use HasUser;

    private array $orderStatuses = [];

    public function run(): void
    {
        $this->authAsAdmin();
        $this->orderStatuses = array_column(OrderStatusEnum::cases(), 'value');
        $organizations = (require database_path('seeders/Additional/data.php'))['organizations'];

        foreach ($organizations as $code => $organization) {
            $this->makeOrdersSpareParts($code);

        }
    }

    private function authAsAdmin(): void
    {
        $admin = $this->getUserAdmin();
        Auth::login($admin);
    }

    private function makeOrdersSpareParts(string $orgCode, int $max = 5): void
    {
        for ($i=0; $i < random_int(1, $max); $i++) {
            $idAuthor = $this->getRandomUser()->id;
            $date = now()->subDays(random_int(1, 50));

            $order = $this->createOrder($orgCode, $idAuthor, $date);

            $this->createOrderSparePartDetails($order);
            $this->createOrderConsumable($order, $idAuthor);
            $this->createOrderMisc($order, $idAuthor, $date);
        }
    }

    private function createOrder(string $orgCode, int $idAuthor, CarbonInterface $date): Order
    {
        return Order::create([
            'org_code' => $orgCode,
            'status' => \Arr::random($this->orderStatuses),
            'comment' => fake()->realText(50),
            'quantity' => random_int(1, 2),
            'requested_by' => $idAuthor,
            'service_request_number' => fake()->randomNumber(6),
            'service_request_date' => now()->subDays(random_int(1, 70)),
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }

    private function createOrderSparePartDetails(Order $order): void
    {
        $printerWs = $this->getPrinterWorkplace($order->org_code);
        $attributes = [
            'id_order' => $order->id,
            'id_printers_workplace' => $printerWs->id,
        ];

        if (random_int(0, 1) === 1) {
            $consumable = $printerWs->consumables()->where('type', ConsumableTypesEnum::other->name)->inRandomOrder()->firstOrFail();
            $attributes['id_spare_part'] = $consumable->id;
            $attributes['call_specialist'] = false;
        } else {
            $attributes['call_specialist'] = true;
        }

        OrderSparePartDetails::create($attributes);
    }

    private function createOrderConsumable(Order $order, int $idAuthor): void
    {
        $consumable = $this->getConsumableCartridge();

        OrderConsumableDetails::create([
            'id_order' => $order->id,
            'id_consumable' => $consumable->id,
            'id_author' => $idAuthor,
            'quantity' => random_int(1, 3),
        ]);
    }

    private function createOrderMisc(Order $order, int $idAuthor, CarbonInterface $date): void
    {
        $printerParts = [
            'Термоголовка',
            'Ролик захвата бумаги',
            'Тормозная площадка',
            'Термопленка',
            'Резиновый вал',
            'Шлейф печатающей головки',
            'Датчик наличия бумаги',
            'Плата форматирования',
            'Ремень каретки',
        ];

        OrderMiscDetails::create([
            'id_order' => $order->id,
            'name' => fake()->randomElement($printerParts),
            'description' => random_int(0, 1) ? fake()->realText(30) : null,
            'created_at' => $date,
            'updated_at' => $date,
            'id_author' => $idAuthor,
        ]);
    }

    private function getConsumableCartridge(): Consumable
    {
        return Consumable::query()->where('type', ConsumableTypesEnum::cartridge->name)->inRandomOrder()->firstOrFail();
    }

    private function getPrinterWorkplace(string $orgCode): PrinterWorkplace
    {
        return PrinterWorkplace::query()
            ->where('org_code', $orgCode)
            ->inRandomOrder()
            ->firstOrFail();
    }

}
