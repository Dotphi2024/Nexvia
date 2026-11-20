<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send FCM Push Notification via Google HTTP v1 API
     *
     * @param string $fcmToken  Target device token
     * @param string $title     Alert title
     * @param string $body      Alert body message
     * @param array  $data      Custom payload (ride_id, status, OTP, etc.)
     * @return bool
     */
    public static function sendNotification(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (empty($fcmToken)) {
            Log::warning('FCM send aborted: Device token is empty.');
            return false;
        }

        try {
            $credentialsPath = env('FIREBASE_CREDENTIALS');
            if (!$credentialsPath || !file_exists($credentialsPath)) {
                $candidates = array_merge(
                    [
                        base_path('firebase-credentials.json'),
                        base_path('firebase-service-account.json'),
                        base_path('firebase.json'),
                        base_path('routemate-3d922-firebase-adminsdk-fbsvc-4c21f8b375.json'),
                    ],
                    glob(base_path('*-firebase-adminsdk-*.json')) ?: []
                );

                $credentialsPath = null;
                foreach ($candidates as $cand) {
                    if (is_string($cand) && file_exists($cand)) {
                        $credentialsPath = $cand;
                        break;
                    }
                }
            }

            if (!$credentialsPath || !file_exists($credentialsPath)) {
                Log::warning('FCM Error: Firebase credentials JSON file not found. Place firebase-credentials.json in backend root.');
                return false;
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);
            $projectId   = $credentials['project_id'];

            // Get OAuth2 Access Token using RS256 signed JWT
            $accessToken = self::getAccessToken($credentials);

            if (!$accessToken) {
                Log::error('FCM Error: Failed to generate OAuth2 Access Token.');
                return false;
            }

            // Stringify data values for FCM v1 payload compatibility
            $stringifiedData = [];
            foreach ($data as $key => $val) {
                $stringifiedData[(string)$key] = is_array($val) ? json_encode($val) : (string)$val;
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => $stringifiedData,
                    'android' => [
                        'priority' => 'HIGH',
                        'notification' => [
                            'sound' => 'default',
                            'channel_id' => 'nexvia_notifications_channel',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'content-available' => 1,
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::info('FCM Notification sent successfully to token: ' . substr($fcmToken, 0, 15) . '...');
                return true;
            } else {
                Log::error('FCM Send Failed: ' . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error('FCM Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Push Notification directly to a Customer model or user_id
     */
    public static function sendToCustomer($customerOrId, string $title, string $body, array $data = []): bool
    {
        $customer = is_numeric($customerOrId) 
            ? \App\Models\Customer::find($customerOrId) 
            : $customerOrId;

        if (!$customer || empty($customer->fcm_token)) {
            return false;
        }

        return self::sendNotification($customer->fcm_token, $title, $body, $data);
    }

    /**
     * Generate OAuth2 Access Token using Service Account Private Key (RS256 JWT)
     */
    private static function getAccessToken(array $credentials): ?string
    {
        try {
            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claimSet = json_encode([
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'exp'   => $now + 3600,
                'iat'   => $now,
            ]);

            $base64UrlHeader   = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlClaimSet = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claimSet));

            $signatureInput = $base64UrlHeader . "." . $base64UrlClaimSet;

            $signature = '';
            openssl_sign($signatureInput, $signature, $credentials['private_key'], 'SHA256');

            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            $jwt = $signatureInput . "." . $base64UrlSignature;

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }

            Log::error('OAuth2 Token Exchange Failed: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('OAuth2 Token Exception: ' . $e->getMessage());
            return null;
        }
    }
}
