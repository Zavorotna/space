<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialPurchase extends Model
{
    protected $fillable = ['material_id', 'user_id', 'price_paid'];

    public function material() { return $this->belongsTo(AdditionalMaterial::class, 'material_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
