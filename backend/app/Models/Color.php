<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'hex_code',
        'sort_order',
        'status',
    ];
    public function variants()
{
    return $this->hasMany(ProductVariant::class);
}
}