<?php
namespace App\Http\Controllers\Middleware;

use Closure; // Untuk meneruskan request ke proses berikutnya
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
