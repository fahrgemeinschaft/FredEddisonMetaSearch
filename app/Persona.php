<?php

namespace App;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
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
        'familyName',
        'gender',
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


    public function contactPoints()
    {
        return $this->belongsToMany(\App\ContactPoint::class);
    }
}
