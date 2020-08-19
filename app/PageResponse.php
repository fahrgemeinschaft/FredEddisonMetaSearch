<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PageResponse extends Model
{
    protected $fillable = [
        'current_page',
        'first_page_url',
        'from',
        'last_page',
        'last_page_url',
        'next_page_url',
        'per_page',
        'prev_page_url',
        'to',
        'total',
    ];
}
