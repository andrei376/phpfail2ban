<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperAgent
 */
class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ip_info_id',
        'agent',
        'action',
        'jail'
    ];
}
