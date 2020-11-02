<?php

namespace App;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class SearchRadius extends Model
{
    use UsesUuid;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'radius',
    ];


    public function location()
    {
        return $this->hasOne(\App\GeoLocation::class);
    }
}
