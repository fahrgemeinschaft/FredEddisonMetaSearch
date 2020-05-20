<?php

namespace App;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Demand extends Model
{
    use UsesUuid;
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
        'availability',
        'availabilityStarts',
        'availabilityEnds',
        'price',
        'priceCurrency',
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
        'availabilityStarts',
        'availabilityEnds',
    ];


    public function trip()
    {
        return $this->hasOne(\App\Trip::class);
    }

    public function persona()
    {
        return $this->hasOne(\App\Persona::class);
    }
}
