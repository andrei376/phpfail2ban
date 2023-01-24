<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    protected $appends = [
        'time_format',
        'time_ago',
    ];

    public function ipinfo()
    {
        // return $this->hasOne(Agent::class);
        return $this->belongsTo(IpInfo::class);
    }

    public function getTimeFormatAttribute()
    {
        if (is_null($this->created_at)) {
            return null;
        }
        return $this->created_at->format('d F Y, H:i:s');
    }

    public function getTimeAgoAttribute()
    {
        if (is_null($this->created_at)) {
            return null;
        }
        return $this->created_at->diffForHumans();
    }
}
