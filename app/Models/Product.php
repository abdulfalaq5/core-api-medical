<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @OA\Schema(
 *     schema="Product",
 *     required={"sku", "name", "price", "stock", "category_id"},
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="Product UUID"
 *     ),
 *     @OA\Property(
 *         property="sku",
 *         type="string",
 *         description="Product SKU (Stock Keeping Unit)"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Product name"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Product price"
 *     ),
 *     @OA\Property(
 *         property="stock",
 *         type="integer",
 *         description="Product stock quantity"
 *     ),
 *     @OA\Property(
 *         property="category_id",
 *         type="string",
 *         format="uuid",
 *         description="Category UUID"
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
 *     ),
 *     @OA\Property(
 *         property="category",
 *         ref="#/components/schemas/Category",
 *         description="Product category"
 *     )
 * )
 */
class Product extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'id',
        'sku',
        'name',
        'stock',
        'price',
        'category_id'
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

    protected $table = 'products';
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getCategoryAttribute()
    {
        return $this->category()->first();
    }

    public function getCategoryIdAttribute()
    {
        return $this->attributes['category_id'] ?? null;
    }

    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : null;
    }
} 