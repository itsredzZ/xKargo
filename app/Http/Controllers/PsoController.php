<?php
// app/Http/Controllers/PsoController.php
// POLA 3: Controller yang me-render halaman Streamlit embed via iFrame.
// Setiap method = satu halaman Streamlit yang di-embed.

namespace App\Http\Controllers;

class PsoController extends Controller
{
    // Base URL Streamlit (dari .env)
    private function streamlitUrl(string $page = ''): string
    {
        $base = config('services.streamlit.public_url', 'http://localhost:8501');
        return $page ? "{$base}/{$page}" : $base;
    }

    /**
     * Input Pesanan → embed halaman 3_Input_Pesanan.py Streamlit
     */
    public function orders()
    {
        return view('pso.embed', [
            'pageTitle'    => 'Input Pesanan',
            'streamlitUrl' => $this->streamlitUrl('3_Input_Pesanan'),
        ]);
    }

    /**
     * Jalankan PSO → embed halaman 5_Optimasi.py Streamlit
     */
    public function run()
    {
        return view('pso.embed', [
            'pageTitle'    => 'Jalankan Optimasi PSO',
            'streamlitUrl' => $this->streamlitUrl('5_Optimasi'),
        ]);
    }

    /**
     * Hasil & Peta → embed halaman 6_Hasil.py Streamlit
     */
    public function results()
    {
        return view('pso.embed', [
            'pageTitle'    => 'Hasil Optimasi & Peta',
            'streamlitUrl' => $this->streamlitUrl('6_Hasil'),
        ]);
    }
}
