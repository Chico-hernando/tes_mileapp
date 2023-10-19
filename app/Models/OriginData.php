<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class OriginData extends Model
{
    protected $connection = 'mongodb';
    protected  $collection = 'origin_data';
    protected $guarded = ['updated_at','created_at'];
    //
}