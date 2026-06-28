@extends('layouts.app')
@section('title', 'Database Barang')
@section('content')

<div class="max-w-7xl mx-auto px-4 py-8 w-full font-sans space-y-8">

    {{-- ============================================ --}}
    {{-- HEADER                                       --}}
    {{-- ============================================ --}}
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-700 flex items-center justify-center shadow-md shadow-blue-100 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Database Barang</h1>
                <p class="text-xs text-slate-500 mt-0.5">Muatan terdaftar otomatis tersinkronisasi dari modul pengiriman</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
    
    {{-- STATS BADGE (Style disamakan persis) --}}
    <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200 rounded-lg px-3 py-1.5 self-start sm:self-auto select-none">
        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        {{ number_format($items->total()) }} data terdaftar
    </span>

    {{-- ACTION BUTTON --}}
    <a href="{{ route('pso.orders') }}" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition-all shadow-xs active:scale-95">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Input Pengiriman
    </a>

</div>
    </div>

    {{-- ============================================ --}}
    {{-- COMPACT HORIZONTAL FILTER BAR                --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_2px_18px_-4px_rgba(0,0,0,0.04)] p-4">
        <form method="GET" action="{{ route('items.index') }}" class="flex flex-wrap items-center gap-3">
            
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider pl-1 pr-2 border-r border-slate-100 hidden md:flex">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </div>

            {{-- Filter Status --}}
            <div class="relative min-w-[160px] flex-1 sm:flex-initial">
                <select name="filter_status" class="w-full appearance-none bg-slate-50/70 hover:bg-slate-50 border border-slate-200/80 text-slate-700 text-xs font-semibold rounded-xl px-3.5 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="menunggu"  {{ request('filter_status') == 'menunggu'  ? 'selected' : '' }}>Menunggu</option>
                    <option value="terkirim"  {{ request('filter_status') == 'terkirim'  ? 'selected' : '' }}>Terkirim</option>
                    <option value="carryover" {{ request('filter_status') == 'carryover' ? 'selected' : '' }}>Carry-over</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            {{-- Filter Depot --}}
            <div class="relative min-w-[170px] flex-1 sm:flex-initial">
                <select name="filter_depot" class="w-full appearance-none bg-slate-50/70 hover:bg-slate-50 border border-slate-200/80 text-slate-700 text-xs font-semibold rounded-xl px-3.5 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="">Semua Depot Asal</option>
                    @foreach($depots as $id => $name)
                        <option value="{{ $id }}" {{ request('filter_depot') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            {{-- Filter Tanggal --}}
            <div class="relative min-w-[150px] flex-1 sm:flex-initial">
                <input type="date" name="filter_date" value="{{ request('filter_date') }}" class="w-full bg-slate-50/70 hover:bg-slate-50 border border-slate-200/80 text-slate-700 text-xs font-semibold rounded-xl px-3.5 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
            </div>

            <div class="flex items-center gap-1.5 ml-auto w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                <button type="submit" class="flex-1 sm:flex-initial bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-bold transition">
                    Terapkan
                </button>
                @if(request()->hasAny(['filter_status', 'filter_depot', 'filter_date']))
                <a href="{{ route('items.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Reset Filter">
                    Reset
                </a>
                @endif
            </div>

        </form>
    </div>

    {{-- ============================================ --}}
    {{-- TABEL DATA                                   --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_2px_18px_-4px_rgba(0,0,0,0.04)] overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Log Item Terdaftar</p>
        </div>

        @if($items->isEmpty())
            <div class="py-16 text-center">
                <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <h3 class="text-xs font-bold text-slate-700">Tidak ada barang ditemukan</h3>
                <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">Sesuaikan parameter filter di atas atau tambahkan pesanan baru lewat modul input.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-bold tracking-wider uppercase text-slate-400 bg-slate-50/50 select-none">
                            <th class="py-3.5 px-6">Order ID</th>
                            <th class="py-3.5 px-6">Nama Barang</th>
                            <th class="py-3.5 px-6 text-center">Dimensi <span class="font-normal text-[10px] lowercase">(p×l×t)</span></th>
                            <th class="py-3.5 px-6 text-right">Berat</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6">Rute Pengiriman</th>
                            <th class="py-3.5 px-6">Tgl Masuk</th>
                            <th class="py-3.5 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-600">
                        @foreach($items as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors group">

                            {{-- ORDER ID --}}
                            <td class="py-3.5 px-6 font-mono text-[11px]">
                                <span class="bg-slate-100 text-slate-600 font-semibold px-2 py-0.5 rounded border border-slate-200/60 tabular-nums">
                                    #{{ $item->order_id }}
                                </span>
                            </td>

                            {{-- NAMA BARANG --}}
                            <td class="py-3.5 px-6 font-bold text-slate-800">
                                {{ $item->name }}
                            </td>

                            {{-- DIMENSI --}}
                            <td class="py-3.5 px-6 text-center text-slate-500 tabular-nums">
                                {{ number_format($item->length_cm, 0) }} × {{ number_format($item->width_cm, 0) }} × {{ number_format($item->height_cm, 0) }} <span class="text-[10px] text-slate-400">cm</span>
                            </td>

                            {{-- BERAT --}}
                            <td class="py-3.5 px-6 text-right font-bold text-slate-700 tabular-nums">
                                {{ number_format($item->weight_kg, 1) }} <span class="font-normal text-slate-400 text-[10px]">kg</span>
                            </td>

                            {{-- STATUS (SHADCN DOT STYLE) --}}
                            <td class="py-3.5 px-6 text-center">
                                @if($item->status === 'menunggu')
                                    <span class="inline-flex items-center gap-1.5 bg-slate-100/80 text-slate-700 border border-slate-200/80 px-2.5 py-0.5 rounded-full text-[11px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Menunggu
                                    </span>
                                @elseif($item->status === 'terkirim')
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200/60 px-2.5 py-0.5 rounded-full text-[11px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Terkirim
                                    </span>
                                @elseif($item->status === 'carryover')
                                    <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-800 border border-amber-200/80 px-2.5 py-0.5 rounded-full text-[11px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Carry-over
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 px-2.5 py-0.5 rounded-full text-[11px] font-semibold">
                                        {{ $item->status }}
                                    </span>
                                @endif
                            </td>

                            {{-- RUTE --}}
                            <td class="py-3.5 px-6 font-semibold text-slate-700">
                                <div class="flex items-center gap-2">
                                    <span>{{ $item->depot_asal ?? '-' }}</span>
                                    <svg class="w-3 h-3 text-slate-300 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    <span>{{ $item->kota_tujuan ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- TANGGAL --}}
                            <td class="py-3.5 px-6 text-slate-400 font-medium tabular-nums text-[11px]">
                                {{ \Carbon\Carbon::parse($item->order_date)->format('d M Y') }}
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-6 text-center">
                                <a href="{{ route('items.edit', $item->id) }}" class="inline-flex items-center gap-1 bg-white hover:bg-slate-50 text-slate-600 hover:text-indigo-600 border border-slate-200/80 px-2.5 py-1 rounded-lg text-[11px] font-bold shadow-2xs transition-all">
                                    <svg class="w-3 h-3 text-slate-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Edit
                                </a>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION FOOTER --}}
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
                <p>
                    Menampilkan <span class="font-bold text-slate-700 tabular-nums">{{ $items->firstItem() ?? 0 }}</span> – <span class="font-bold text-slate-700 tabular-nums">{{ $items->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-700 tabular-nums">{{ number_format($items->total()) }}</span> data
                </p>
                <div>
                    {{ $items->links() }}
                </div>
            </div>
        @endif

    </div>
</div>
@endsection