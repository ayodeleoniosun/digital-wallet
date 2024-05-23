<?php

namespace App\Domains\Utils\Traits;

use App\Models\ActivityLog;
use App\Models\User;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

trait ActivityTrait
{
    public function setActivity(string $type, User $user, $getMeta = true): void
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return;
        }

        $agent = new Agent();

        $location = Location::get();

        if ($getMeta) {
            $meta = json_encode([
                'platform' => $agent->platform().' '.$agent->version($agent->platform()),
                'browser' => $agent->browser().' '.$agent->version($agent->browser()),
                'device' => $agent->device(),
                'ip' => $location?->ip,
                'location' => [
                    'country' => $location?->countryName,
                    'city' => $location?->cityName,
                    'region' => $location?->regionName,
                ],
            ]);
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => $type,
            'meta' => $getMeta ? $meta : null,
        ]);
    }
}
