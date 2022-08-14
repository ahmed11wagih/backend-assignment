<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $fillable = ['REMOTE_ADDR','HTTP_X_FORWARDED_FOR','logged_at'];

    public $timestamps = false;
}
