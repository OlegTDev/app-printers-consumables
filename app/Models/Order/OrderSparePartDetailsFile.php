<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_spare_part_order_detail
 * @property string $filename
 * @property string $created_at
 */
class OrderSparePartDetailsFile extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'order_spare_part_details_files';    

}
