<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderSparePartDetails;
use App\Models\Order\OrderSparePartDetailsFile;
use App\Models\Order\OrderStatusEnum;
use App\Models\Organization;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderSparePartDetailsTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create(['code' => 'T0001', 'name' => 'Test Org']);
        $this->adminUser = User::factory()->create(['name' => 'admin_test', 'email' => 'admin_test@test.com', 'fio' => 'Админ', 'org_code' => 'T0001']);
        Auth::login($this->adminUser);
    }

    private function createOrder(string $status = null): Order
    {
        return Order::factory()
            ->for($this->organization)
            ->create(['status' => $status ?? OrderStatusEnum::default()]);
    }

    private function createPrinterWorkplace(array $attributePrinter = [], array $attributeWs = []): PrinterWorkplace
    {
        $printer = Printer::factory()->create([
            'vendor' => 'HP',
            'model' => 'Model 1',
            'is_color_print' => true,
            'id_author' => $this->adminUser->id,
            ...$attributePrinter,
        ]);
        return PrinterWorkplace::factory()->for($printer)->create([
            'id_author' => $this->adminUser->id,
            'org_code' => 'T0001',
            'location' => '111',
            'inventory_number' => '000001',
            ...$attributeWs,
        ]);
    }

    public function test_it_belongs_to_parent_order_and_printer_workplace(): void
    {
        $order = $this->createOrder();
        $printerWs = $this->createPrinterWorkplace();
        $orderSparePartDetails = OrderSparePartDetails::factory()
            ->for($order)
            ->for($printerWs, 'printerWorkplace')
            ->create();

        $this->assertTrue($order->is($orderSparePartDetails->order));
        $this->assertTrue($printerWs->is($orderSparePartDetails->printerWorkplace));
    }

    public function test_it_belongs_to_consumable_with_type_other(): void
    {
        $order = $this->createOrder();
        $printerWs = $this->createPrinterWorkplace();
        $consumable = Consumable::factory()->create(['type' => ConsumableTypesEnum::other->name, 'name' => 'Some consumable']);
        $orderSparePartDetails = OrderSparePartDetails::factory()
            ->for($order)
            ->for($consumable, 'sparePart')
            ->for($printerWs, 'printerWorkplace')
            ->create();

        $this->assertTrue($consumable->is($orderSparePartDetails->sparePart));
    }

    public function test_it_has_many_files(): void
    {
        $order = $this->createOrder();
        $printerWs = $this->createPrinterWorkplace();
        /** @var OrderSparePartDetails */
        $orderSparePartDetails = OrderSparePartDetails::factory()
            ->for($order)
            ->for($printerWs, 'printerWorkplace')
            ->create();

        /** @var OrderSparePartDetailsFile */
        $file1 = OrderSparePartDetailsFile::factory()->create([
            'id_spare_part_order_detail' => $orderSparePartDetails,
            'filename' => 'file1',
        ]);
        /** @var OrderSparePartDetailsFile */
        $file2 = OrderSparePartDetailsFile::factory()->create([
            'id_spare_part_order_detail' => $orderSparePartDetails,
            'filename' => 'file2',
        ]);

        $this->assertCount(2, $orderSparePartDetails->files);
        $this->assertEqualsCanonicalizing([$file1->filename, $file2->filename], $orderSparePartDetails->files->pluck('filename')->all());
    }

    public function test_it_filtered_by_spare_part_name(): void
    {
        $order = $this->createOrder();
        $printerWs = $this->createPrinterWorkplace();
        $sparePart = Consumable::factory()->create([
            'type' => ConsumableTypesEnum::other->name,
            'name' => 'Some consumable',
            'description' => 'Description consumable',
        ]);
        /** @var OrderSparePartDetails */
        $orderSparePartDetails = OrderSparePartDetails::factory()
            ->for($order)
            ->for($sparePart, 'sparePart')
            ->for($printerWs, 'printerWorkplace')
            ->create();

        $resultFilterName = OrderSparePartDetails::query()->filter(['search' => 'some consumable'])->get();
        $this->assertCount(1, $resultFilterName);
        $this->assertEqualsCanonicalizing([$orderSparePartDetails->id], $resultFilterName->pluck('id')->all());

        $resultFilterDescription = OrderSparePartDetails::query()->filter(['search' => 'Description'])->get();
        $this->assertCount(1, $resultFilterDescription);
        $this->assertEqualsCanonicalizing([$orderSparePartDetails->id], $resultFilterDescription->pluck('id')->all());

        $resultFilterNull = OrderSparePartDetails::query()->filter(['search' => 'not found text'])->get();
        $this->assertCount(0, $resultFilterNull);
    }

    public function test_it_filtered_by_printer_workplace_location_or_serial_number_or_inventory_number(): void
    {
        $order = $this->createOrder();
        $printerWs = $this->createPrinterWorkplace(attributeWs: ['location' => '159 каб.', 'serial_number' => '123456789', 'inventory_number' => '543219876']);
        /** @var OrderSparePartDetails */
        $orderSparePartDetails = OrderSparePartDetails::factory()
            ->for($order)
            ->for($printerWs, 'printerWorkplace')
            ->create();

        $resultFilterLocation = OrderSparePartDetails::query()->filter(['search' => '159'])->get();
        $this->assertCount(1, $resultFilterLocation);
        $this->assertEqualsCanonicalizing([$orderSparePartDetails->id], $resultFilterLocation->pluck('id')->all());

        $resultFilterSerialNumber = OrderSparePartDetails::query()->filter(['search' => '123456789'])->get();
        $this->assertCount(1, $resultFilterSerialNumber);
        $this->assertEqualsCanonicalizing([$orderSparePartDetails->id], $resultFilterSerialNumber->pluck('id')->all());

        $resultFilterInventoryNumber = OrderSparePartDetails::query()->filter(['search' => '543219876'])->get();
        $this->assertCount(1, $resultFilterInventoryNumber);
        $this->assertEqualsCanonicalizing([$orderSparePartDetails->id], $resultFilterInventoryNumber->pluck('id')->all());

        $resultFilterNotFound = OrderSparePartDetails::query()->filter(['search' => 'not-found-text'])->get();
        $this->assertCount(0, $resultFilterNotFound);
    }

    public function test_it_filtered_by_printer_vendor_or_model(): void
    {
        $order = $this->createOrder();
        $printerWs = $this->createPrinterWorkplace(attributePrinter: ['vendor' => 'Xerox Printer', 'model' => 'VersaLink C7000']);
        /** @var OrderSparePartDetails */
        $orderSparePartDetails = OrderSparePartDetails::factory()
            ->for($order)
            ->for($printerWs, 'printerWorkplace')
            ->create();

        $resultFilterVendor = OrderSparePartDetails::query()->filter(['search' => 'Xerox'])->get();
        $this->assertCount(1, $resultFilterVendor);
        $this->assertEqualsCanonicalizing([$orderSparePartDetails->id], $resultFilterVendor->pluck('id')->all());

        $resultFilterModel = OrderSparePartDetails::query()->filter(['search' => 'C7000'])->get();
        $this->assertCount(1, $resultFilterModel);
        $this->assertEqualsCanonicalizing([$orderSparePartDetails->id], $resultFilterModel->pluck('id')->all());
    }

    public function test_it_filtered_by_order_status(): void
    {
        $orderPending = $this->createOrder(OrderStatusEnum::STATUS_PENDING->value);
        $orderOrdered = $this->createOrder(OrderStatusEnum::STATUS_ORDERED->value);
        $printerWs = $this->createPrinterWorkplace(attributePrinter: ['vendor' => 'Xerox Printer', 'model' => 'VersaLink C7000']);

        /** @var OrderSparePartDetails */
        $orderSparePartDetailsPending = OrderSparePartDetails::factory()
            ->for($orderPending)
            ->for($printerWs, 'printerWorkplace')
            ->create();

        /** @var OrderSparePartDetails */
        $orderSparePartDetailsOrdered = OrderSparePartDetails::factory()
            ->for($orderOrdered)
            ->for($printerWs, 'printerWorkplace')
            ->create();

        $resultFilterPending = OrderSparePartDetails::query()->filter(['status' => OrderStatusEnum::STATUS_PENDING->value])->get();
        $this->assertCount(1, $resultFilterPending);
        $this->assertEquals($orderSparePartDetailsPending->id, $resultFilterPending->first()->id);

        $resultFilterOrdered = OrderSparePartDetails::query()->filter(['status' => OrderStatusEnum::STATUS_ORDERED->value])->get();
        $this->assertCount(1, $resultFilterOrdered);
        $this->assertEquals($orderSparePartDetailsOrdered->id, $resultFilterOrdered->first()->id);
    }

    public function test_it_filtered_by_organizations_codes(): void
    {
        $organization2 = Organization::factory()->create(['code' => 'T0002', 'name' => 'Org 2']);
        $order1 = $this->createOrder();
        $order2 = Order::factory()->for($organization2)->create(['status' => OrderStatusEnum::STATUS_COMPLETED->value]);
        $printerWs = $this->createPrinterWorkplace(attributePrinter: ['vendor' => 'Xerox Printer', 'model' => 'VersaLink C7000']);

        $orderSparePartDetails1 = OrderSparePartDetails::factory()
            ->for($order1)
            ->for($printerWs, 'printerWorkplace')
            ->create();

        $orderSparePartDetails2 = OrderSparePartDetails::factory()
            ->for($order2)
            ->for($printerWs, 'printerWorkplace')
            ->create();

        $resultFilter1 = OrderSparePartDetails::query()->filter(['organizations' => [$organization2->code]])->get();
        $this->assertCount(1, $resultFilter1);
        $this->assertEquals($orderSparePartDetails2->id, $resultFilter1->first()->id);

        $resultFilter2 = OrderSparePartDetails::query()->filter(['organizations' => [$this->organization->code, $organization2->code]])->get();
        $this->assertCount(2, $resultFilter2);
        $this->assertEqualsCanonicalizing([$orderSparePartDetails1->id, $orderSparePartDetails2->id], $resultFilter2->pluck('id')->all());
    }

    public function test_it_returns_all_when_no_filters(): void
    {
        $count = 3;
        $order = $this->createOrder();
        $printerWs = $this->createPrinterWorkplace();
        OrderSparePartDetails::factory()->for($order)->for($printerWs, 'printerWorkplace')->count($count)->create();
        $results = OrderSparePartDetails::query()->filter([])->get();
        $this->assertCount($count, $results);
    }

}
