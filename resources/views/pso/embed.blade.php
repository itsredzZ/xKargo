{{-- resources/views/pso/embed.blade.php --}}
{{-- POLA 3: iFrame embed halaman Streamlit tertentu di dalam layout Laravel --}}
{{-- Halaman ini dipanggil untuk: Input Pesanan, Jalankan PSO, dan Hasil --}}

@extends('layouts.app')

@section('title', $pageTitle)

{{-- Tandai sebagai fullscreen agar tidak ada padding di main --}}
@section('fullscreen', 'fullscreen-iframe')

@push('styles')
<style>
    .fullscreen-iframe { padding: 0 !important; }
    #iframe-wrapper { position: relative; width: 100%; height: calc(100vh - 64px); }
    #streamlit-frame { border: none; width: 100%; height: 100%; display: block; }
    #frame-loader {
        position: absolute; inset: 0;
        background: #f8fafc;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 12px; z-index: 10;
        transition: opacity 0.3s;
    }
    #frame-loader.hidden { opacity: 0; pointer-events: none; }
    .spinner {
        width: 40px; height: 40px;
        border: 3px solid #dbeafe;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    /* Banner info integrasi */
    #integration-banner {
        position: absolute; top: 8px; right: 12px; z-index: 20;
        background: rgba(255,255,255,0.95); border: 1px solid #e2e8f0;
        border-radius: 8px; padding: 6px 12px;
        font-size: 11px; color: #64748b;
        display: flex; align-items: center; gap: 6px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    }
    .dot-live { width:7px; height:7px; background:#22c55e;
                border-radius:50%; animation: pulse-dot 1.5s ease-in-out infinite; }
    @keyframes pulse-dot {
        0%,100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(0.8); }
    }
</style>
@endpush

@section('content')
<div id="iframe-wrapper">

    {{-- Loading overlay --}}
    <div id="frame-loader">
        <div class="spinner"></div>
        <p class="text-sm text-gray-500">Memuat {{ $pageTitle }}...</p>
        <p class="text-xs text-gray-400">Menghubungkan ke PSO Engine...</p>
    </div>

    {{-- Banner status koneksi --}}
    <div id="integration-banner">
        <div class="dot-live"></div>
        <span>PSO Engine aktif — Streamlit :8501</span>
    </div>

    {{--
        iFrame Streamlit.
        src: URL Streamlit dengan parameter ?embed=true agar header Streamlit
             tersembunyi (hanya konten yang tampil).
        Param tambahan:
          ?embed_options=show_toolbar → tetap tampilkan toolbar Streamlit
          &page=...                   → deep-link ke halaman tertentu (jika pakai multi-page)
    --}}
    <iframe
        id="streamlit-frame"
        src="{{ $streamlitUrl }}?embed=true&embed_options=hide_loading_screen"
        title="{{ $pageTitle }}"
        allow="clipboard-write"
        sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-downloads"
        onload="hideLoader()"
    ></iframe>
</div>
@endsection

@push('scripts')
<script>
    function hideLoader() {
        const loader = document.getElementById('frame-loader');
        loader.classList.add('hidden');
        setTimeout(() => loader.remove(), 400);
    }

    // Fallback: sembunyikan loader setelah 8 detik jika onload tidak trigger
    setTimeout(hideLoader, 8000);

    // Sampaikan token auth ke iFrame via postMessage (opsional, Pola 2+3 hybrid)
    // iFrame Streamlit bisa pakai ini untuk memanggil API Laravel tanpa login ulang.
    const frame = document.getElementById('streamlit-frame');
    frame.addEventListener('load', function () {
        frame.contentWindow.postMessage({
            type:      'xkargo_auth',
            api_url:   '{{ config('app.url') }}/api',
            api_token: '{{ config('services.streamlit.api_token') }}',
            user:      '{{ auth()->user()->username ?? '' }}',
        }, '{{ env('STREAMLIT_PUBLIC_URL', 'http://localhost:8501') }}');
    });
</script>
@endpush
