<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use MongoDB\Laravel\Eloquent\Model;

class Transaction extends Model
{

    use HasUuids;
    protected $connection = 'mongodb';
    protected  $collection = 'transaction';
    protected $primaryKey = 'transaction_id';

    protected $guarded = [];
}