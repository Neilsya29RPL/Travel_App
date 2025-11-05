<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $table = 'destinations';
    protected $primaryKey = 'destination_id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'country_id',
        'category_id',
        'mood_id',
        'description',
        'average_cost',
    ];

    public function accommodations()
    {
        return $this->hasMany(Accommodation::class, 'destination_id', 'destination_id');
    }
}