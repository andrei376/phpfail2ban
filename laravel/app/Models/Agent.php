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
        'row_count_format',
        'total_db_ip_format'
    ];

    public function ipinfo()
    {
        // return $this->hasOne(Agent::class);
        return $this->belongsTo(IpInfo::class);
    }

    public function getRowCountFormatAttribute(): ?string
    {
        if (is_null($this->row_count)) {
            return null;
        }

        return number_format ($this->row_count,  0 ,  "," ,  "." );
    }

    public function getTotalDbIpFormatAttribute(): ?string
    {
        if (is_null($this->total_ip)) {
            return null;
        }

        return number_format ($this->total_ip ,  0 ,  "," ,  "." );
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
