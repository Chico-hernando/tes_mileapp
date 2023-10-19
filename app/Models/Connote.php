<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use MongoDB\Laravel\Eloquent\Model;

class Connote extends Model
{
    use HasUuids;
    protected $connection = 'mongodb';
    protected  $collection = 'connote';
    protected $primaryKey = 'connote_id';

    protected $guarded = [];
}