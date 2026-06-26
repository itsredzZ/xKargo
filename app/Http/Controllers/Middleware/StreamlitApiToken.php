<?php
// app/Http/Middleware/StreamlitApiToken.php
// ─────────────────────────────────────────
// POLA 2 — Keamanan API
// Streamlit kirim header: Authorization: Bearer <token>
// Token disimpan di .env → STREAMLIT_API_TOKEN
//
// Cara set token di Streamlit (.env atau st.secrets):
//   LARAVEL_API_URL=http://localhost:8000
//   LARAVEL_API_TOKEN=your-secret-token
//
// Cara pakai di Streamlit:
//   import os, requests
//   headers = {"Authorization": f"Bearer {os.getenv('LARAVEL_API_TOKEN')}"}
//   r = requests.get(f"{os.getenv('LARAVEL_API_URL')}/api/trucks", headers=headers)

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StreamlitApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token || $token !== config('services.streamlit.api_token')) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'Token API tidak valid. Set STREAMLIT_API_TOKEN di .env Laravel dan LARAVEL_API_TOKEN di .env Streamlit.',
            ], 401);
        }

        return $next($request);
    }
}
