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
        try {
            if (empty($this->agent)) {
                return $show === 'browser' ? 'Web Browser' : 'Desktop';
            }
            if (class_exists(Agent::class)) {
                $agent = new Agent;
                $agent->setUserAgent($this->agent);
                $result = $agent->$show();
                if (is_string($result) && $result !== '') {
                    return $result;
                }
            }
        } catch (\Throwable $e) {
            // Fallback gracefully
        }

        return $show === 'browser' ? 'Web Browser' : 'Desktop';
    }

    public function getBrowserAttribute(): string
    {
        try {
            $val = $this->getAgent('browser');
            return (is_string($val) && $val !== '') ? $val : 'Web Browser';
        } catch (\Throwable $e) {
            return 'Web Browser';
        }
    }

    public function getPlatformAttribute(): string
    {
        try {
            $val = $this->getAgent('platform');
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
