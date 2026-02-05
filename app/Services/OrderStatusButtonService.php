<?php
namespace App\Services;


class OrderStatusButtonService
{
    /**
     * @var array<string, array{
     *     label: string,
     *     next: list<string>,
     *     roles: list<string>,
     *     color: string
     * }>
     */
    private $statusConfig;

    public function __construct()
    {
        $this->statusConfig = config('order_statuses');
    }

    public function getAvailableButtons(string $currentStatus, array $userRoles)
    {
        if (!isset($this->statusConfig[$currentStatus])) {
            return [];
        }

        $availableButtons = [];

        $nextStatuses = $this->statusConfig[$currentStatus]['next'];



        foreach ($nextStatuses as $nextStatus) {
            if (!isset($this->statusConfig[$nextStatus])) {
                continue;
            }
            $requiredRoles = $this->statusConfig[$nextStatus]['roles'];
            if ($this->isAccessible($userRoles, $requiredRoles)) {
                $availableButtons[] = $nextStatus;
            }
        }

        return $availableButtons;
    }

    private function isAccessible(array $userRoles, array $requiredRoles)
    {
        return !empty(array_intersect($userRoles, ['admin', ...$requiredRoles]));
    }

}
