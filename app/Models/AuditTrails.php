<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrails extends Model
{
    protected $fillable = ['user_id', 'action', 'resource', 'details'];
}
