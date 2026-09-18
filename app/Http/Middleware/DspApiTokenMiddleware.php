<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\DspApplication;
use Symfony\Component\HttpFoundation\Response;

class DspApiTokenMiddleware
{
    /**
     * Handle an incoming request for protected DSP APIs.
     *
     * Expects: Header "Authorization: Bearer <token>" or parameter "token" / "dsp_id"
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $trimmedJson = [];
        if (is_array($bodyJson)) {
            foreach ($bodyJson as $key => $val) {
                $trimmedJson[trim($key)] = $val;
            }
        }

        $dspId = $request->input('dsp_id')
            ?? $request->input('id')
            ?? ($trimmedJson['dsp_id'] ?? null)
            ?? ($trimmedJson['id'] ?? null);

        $token = $request->bearerToken()
            ?? $request->input('token')
            ?? $request->header('token')
            ?? ($trimmedJson['token'] ?? null)
            ?? ($trimmedJson['api_token'] ?? null)
            ?? ($trimmedJson['access_token'] ?? null);

        $dsp = null;

        if (!empty($token)) {
            $dsp = DspApplication::where('api_token', $token)->first();
        }

        if (!$dsp && !empty($dspId)) {
            $dsp = DspApplication::find($dspId);
        }

        if (empty($dspId) && empty($token)) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. DSP Authorization token or dsp_id is required.',
            ], 401);
        }

        if (!$dsp) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Invalid DSP credentials or token.',
            ], 401);
        }

        if ($dsp->status === 'rejected') {
            return response()->json([
                'status'  => false,
                'message' => 'Your DSP partner account is not active. Status: Rejected.',
            ], 403);
        }

        // Attach authenticated DSP partner to request
        $request->attributes->set('authenticated_dsp', $dsp);
        $request->setUserResolver(fn() => $dsp);
        auth()->guard('dsp')->setUser($dsp);

        return $next($request);
    }
}
