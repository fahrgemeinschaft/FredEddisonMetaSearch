<?php

namespace App;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Transport extends Model
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
        'transportType',
        'seatingCapacity',
        'cargoVolume',
        'owner',
        'operator',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted' => 'integer',
        'owner' => 'integer',
        'operator' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created',
        'modified',
    ];


    public function owner()
    {
        return $this->hasOne(\App\Persona::class);
    }

    public function operator()
    {
        return $this->hasOne(\App\Persona::class);
    }
}
