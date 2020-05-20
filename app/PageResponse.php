<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PageResponse extends Model
{
    protected $fillable = [
        'page',
        'pageSize',
        'totalCount',
        'lastIndex',
        'first',
        'last',
        'firstIndex'
    ];
}
