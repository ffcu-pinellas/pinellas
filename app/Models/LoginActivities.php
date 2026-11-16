<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;

class LoginActivities extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['browser', 'platform'];

    public static function add($user_id = null)
    {
        $model = new static;
        $model->user_id = $user_id ?? auth()->id();
        $model->ip = request()->ip();
        $model->location = getLocation()->name;
        $model->agent = request()->userAgent();
        $model->save();

        return $model;
    }

    private function getAgent($show)
    {
        $agent = new Agent;
        $agent->setUserAgent($this->agent);

        return $agent->$show();
    }

    public function getBrowserAttribute(): string
    {
        try {
            $val = self::getAgent('browser');
            return (is_string($val) && $val !== '') ? $val : 'Web Browser';
        } catch (\Throwable $e) {
            return 'Web Browser';
        }
    }

    public function getPlatformAttribute(): string
    {
        try {
            $val = self::getAgent('platform');
            return (is_string($val) && $val !== '') ? $val : 'Desktop';
        } catch (\Throwable $e) {
            return 'Desktop';
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
