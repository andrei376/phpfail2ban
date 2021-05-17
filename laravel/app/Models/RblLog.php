<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperRblLog
 */
class RblLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'type',
        'read',
        'message'
    ];

    protected $casts = [
        'date' => 'datetime',
        'read' => 'boolean',
    ];

    protected $appends = [
        'date_format',
        'date_ago'
    ];

    public function getDateFormatAttribute()
    {
        return $this->date->format('d F Y, H:i:s');
    }

    public function getDateAgoAttribute()
    {
        return $this->date->diffForHumans();
    }

    public static function saveLog($user = null, $type = null, $message = null)
    {
        $log['id'] = '';
        $log['user'] = $user;
        $log['type'] = $type;
        $log['message'] = $message;

        if (!is_null($message)) {
            self::create($log);
        }
    }
}
