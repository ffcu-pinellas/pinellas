<?php

namespace App\Http\Controllers;

use App\Models\EmailTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TrackingController extends Controller
{
    public function openPixel($token)
    {
        $tracking = EmailTracking::where('tracking_token', $token)->first();

        if ($tracking && $tracking->status !== 'opened') {
            $tracking->update([
                'status' => 'opened',
                'opened_at' => Carbon::now()
            ]);
        }

        // Return a 1x1 transparent GIF
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($gif)->header('Content-Type', 'image/gif');
    }
}
