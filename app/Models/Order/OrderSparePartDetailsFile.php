<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

/**
 * @property int $id
 * @property int $id_spare_part_order_detail
 * @property string $filename
 * @property string $created_at
 * @method static \Database\Factories\Order\OrderSparePartDetailsFileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderSparePartDetailsFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderSparePartDetailsFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderSparePartDetailsFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderSparePartDetailsFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderSparePartDetailsFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderSparePartDetailsFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderSparePartDetailsFile whereIdSparePartOrderDetail($value)
 * @mixin \Eloquent
 */
class OrderSparePartDetailsFile extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'order_spare_part_details_files';

    public static function booted()
    {
        static::deleting(function (OrderSparePartDetailsFile $model) {
            Storage::delete($model->filename);
        });
    }

}
