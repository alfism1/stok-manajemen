<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockTransaction; // Assuming StockTransaction is in the same namespace or needs to be imported

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'current_stock',
        'minimum_stock',
        'attributes',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected $casts = [
        'attributes' => 'array',
    ];

    public function transactions()
    {
        return $this->hasMany(StockTransaction::class);
    }
}
