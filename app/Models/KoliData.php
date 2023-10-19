<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use MongoDB\Laravel\Eloquent\Model;

class KoliData extends Model
{
    use HasUuids;
    protected $connection = 'mongodb';
    protected  $collection = 'koli_data';
    protected $primaryKey = 'koli_id';
    protected $guarded = [];
}