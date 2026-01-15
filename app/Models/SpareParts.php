<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $id_author
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property User $author
 */
class SpareParts extends Model
{
    use HasFactory;

    protected $table = 'spare_parts';

    protected $fillable = [
        'name',
        'description',        
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function(self $model) {
            $model->id_author = Auth::id();
        });        
    }

    public static function labels()
    {
        return [
            'name' => 'Наименование',
            'description' => 'Описание',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'timestamps' => 'Дата',
            'author' => 'Автор',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author');
    }

    public function scopeFilter(Builder $query, array $filters)
    {               
        $query->with('author');
        $query->when($filters['search'] ?? null, function (Builder $query, $search) {
            $query->whereAny(['name', 'description'], 'ILIKE', "%$search%");
        });
        $query->orderByDesc('created_at')->orderByDesc('updated_at');
    }
}
