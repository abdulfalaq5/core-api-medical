<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @OA\Schema(
 *     schema="Category",
 *     required={"name"},
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="Category UUID"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Category name"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation timestamp"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Last update timestamp"
 *     )
 * )
 */
class Category extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'id',
        'name'
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $table = 'categories';

    public function products()
    {
        return $this->hasMany(Product::class);
    }
} 