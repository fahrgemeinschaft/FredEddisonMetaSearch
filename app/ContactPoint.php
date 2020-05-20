<?php

namespace App;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class ContactPoint extends Model
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
        'email',
        'faxnumber',
        'telephone',
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
    ];
}
