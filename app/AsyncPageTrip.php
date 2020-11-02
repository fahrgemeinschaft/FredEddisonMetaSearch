<?php

namespace App;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class AsyncPageTrip extends Model
{
    use UsesUuid;

    protected $fillable = [
        'id',
        'results',
        'page',
        'total'
    ];

}
