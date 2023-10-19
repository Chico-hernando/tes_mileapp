<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DestinationData extends Model
{
    protected $connection = 'mongodb';
    protected  $collection = 'destination_data';
    protected $guarded = ['updated_at','created_at'];
    //
}