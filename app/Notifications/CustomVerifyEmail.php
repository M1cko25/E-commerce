<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class CustomVerifyEmail extends VerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        // Always use HTTPS in production with the correct domain
        $baseUrl = 'https://drm-hardware.com';

        $routeName = 'verification.verify';

        $url = URL::temporarySignedRoute(
            $routeName,
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Extract the path from the generated URL and append it to our production base URL
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';

        return $baseUrl . $path . $query;
    }
}
