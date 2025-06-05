<?php

namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;

class Doctor extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'doctors'; // nombre exacto de la colección en Atlas
}
