<?php

namespace Tests\Unit\Services\Order;

use App\Services\Order\OrderStatusButtonService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OrderStatusButtonServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('order_statuses', [
            'pending' => [
                'next' => ['rejected', 'agreed'],
                'roles' => ['approver'],
            ],
            'rejected' => [
                'next' => ['no-way'],
                'roles' => ['approver','executor'],
            ],
            'agreed' => [
                'next' => ['completed'],
                'roles' => ['approver'],
            ],
            'completed' => [
                'next' => [],
                'roles' => [],
            ],
        ]);
    }

    public function test_it_can_available_status_button_as_admin_role(): void
    {
        $service = app(OrderStatusButtonService::class);
        $this->assertEqualsCanonicalizing(['rejected', 'agreed'], $service->getAvailableButtons('pending', ['admin']));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('rejected', ['admin']));
        $this->assertEqualsCanonicalizing(['completed'], $service->getAvailableButtons('agreed', ['admin']));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('completed', ['admin']));
    }

    public function test_it_can_available_status_button_as_approver_role(): void
    {
        $service = app(OrderStatusButtonService::class);
        $this->assertEqualsCanonicalizing(['rejected', 'agreed'], $service->getAvailableButtons('pending', ['approver']));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('rejected', ['approver']));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('agreed', ['approver']));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('completed', ['approver']));
    }

    public function test_it_can_available_status_button_as_executor_role(): void
    {
        $service = app(OrderStatusButtonService::class);
        $this->assertEqualsCanonicalizing(['rejected'], $service->getAvailableButtons('pending', ['executor']));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('rejected', ['executor']));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('agreed', ['executor']));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('completed', ['executor']));
    }

    public function test_it_can_available_status_button_as_regular_user(): void
    {
        $service = app(OrderStatusButtonService::class);
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('pending', []));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('rejected', []));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('agreed', []));
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('completed', []));
    }

    public function test_it_can_available_not_exists_status_button(): void
    {
        $service = app(OrderStatusButtonService::class);
        $this->assertEqualsCanonicalizing([], $service->getAvailableButtons('not-exists-status', []));
    }

}
