<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    protected $table = 'accommodations';
    protected $primaryKey = 'accommodation_id';
    public $timestamps = true;

    protected $fillable = [
        'destination_id',
        'name',
        'type',
        'price_per_night',
        'rating',
        'provider',
        'external_id',
    ];

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'destination_id');
    }
}