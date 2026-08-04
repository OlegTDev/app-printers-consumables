<?php

namespace Tests\Unit\Models\Order;

use App\Models\Auth\User;
use App\Models\Order\Order;
use App\Models\Order\OrderSparePartDetails;
use App\Models\Order\OrderSparePartDetailsFile;
use App\Models\Order\OrderStatusEnum;
use App\Models\Organization;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderSparePartDetailsFileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Фейкуем диск 'public' или 'local' — в зависимости от вашей настройки
        Storage::fake();
    }

    private function createOrderSparePartDetails(): OrderSparePartDetails
    {
        $organization = Organization::factory()->create(['code' => '01', 'name' => 'Org 1']);

        $user = User::factory()->create(['name' => 'admin', 'email' => 'admin@test.com', 'org_code' => $organization]);

        $order = Order::withoutEvents(static fn() => Order::factory()
            ->for($organization)
            ->for($user, 'requested')
            ->create(['status' => OrderStatusEnum::default()]));

        $printer = Printer::factory()->for($user, 'author')->create(['vendor' => 'HP', 'model' => 'Model_1']);
        $printerWs = PrinterWorkplace::factory()
            ->for($printer)
            ->for($organization)
            ->for($user, 'author')
            ->create(['location' => '1', 'inventory_number' => '123456789']);

        return OrderSparePartDetails::factory()
            ->for($order)
            ->for($printerWs)
            ->create();
    }

    public function test_it_deletes_file_from_storage_when_model_is_deleted(): void
    {
        $orderDetail = $this->createOrderSparePartDetails();

        $filename = 'spare-parts/reports/report.pdf';

        $fileModel = OrderSparePartDetailsFile::factory()->create([
            'id_spare_part_order_detail' => $orderDetail->id,
            'filename' => $filename,
        ]);

        // Убедимся, что файл "загружен" (в фейковом хранилище)
        Storage::put($filename, 'fake content');
        Storage::assertExists($filename);

        // Удаляем модель
        $fileModel->delete();

        // Проверяем:
        // 1. Модель удалена из БД
        $this->assertModelMissing($fileModel);

        // 2. Файл удалён из хранилища
        Storage::assertMissing($filename);
    }

    public function test_it_does_not_fail_if_file_does_not_exist_in_storage(): void
    {
        $orderDetail = $this->createOrderSparePartDetails();

        $fileModel = OrderSparePartDetailsFile::factory()->create([
            'id_spare_part_order_detail' => $orderDetail->id,
            'filename' => 'spare-parts/missing/file.txt',
        ]);

        // Файл НЕ загружается — его нет в хранилище

        // Удаляем модель — не должно быть ошибки
        $fileModel->delete();

        // Проверяем, что модель удалилась
        $this->assertModelMissing($fileModel);
    }
}
