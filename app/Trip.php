<?php

namespace App;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    // use UsesUuid;
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created',
        'modified',
        'deleted',
        'createdBy',
        'modifiedBy',
        'url',
        'additionalType',
        'name',
        'image',
        'description',
        'departureTime',
        'arrivalTime',
        'availableSeats',
        'connector',
        'smoking',
        'animals',
        'offer',
        'demand',
        'transport',
        'participation',
        'search',
        'startPoint',
        'endPoint',
        'timestamp'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted' => 'integer',
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created',
        'modified',
        'arrivalTime',
        'departureTime',
    ];

    public function offer()
    {
        return $this->hasOne(\App\Offer::class);
    }

    public function demand()
    {
        return $this->hasOne(\App\Demand::class);
    }

    public function transport()
    {
        return $this->hasOne(\App\Transport::class);
    }

    public function participation()
    {
        return $this->hasOne(\App\Participation::class);
    }

    public function search()
    {
        return $this->belongsTo(Search::class);
    }

    public function startPoint()
    {
        return $this->hasOne(GeoLocation::class);
    }

    public function endPoint()
    {
        return $this->hasOne(GeoLocation::class);
    }

}
