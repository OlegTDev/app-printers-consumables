<?php
declare(strict_types=1);

namespace App\Models\Order;

abstract class SubOrderContract extends \Illuminate\Database\Eloquent\Model
{
    abstract public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo;
}
