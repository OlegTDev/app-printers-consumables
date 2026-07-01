<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class Manufacturer extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'manufacturers';

}
