<?php
// app/Http/Controllers/SettingsController.php
// Admin edit parameter PSO dan operasional dari UI Laravel.
// Perubahan langsung tersimpan ke db_xkargo.settings
// dan akan dibaca PSO engine Streamlit pada run berikutnya.

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $pso         = Setting::getPsoParams();
        $operasional = Setting::getOperasionalParams();
        $breadcrumb  = ['Parameter PSO' => null];

        return view('settings.index', compact('pso', 'operasional', 'breadcrumb'));
    }

    public function update(Request $request)
    {
        $request->validate([
            // PSO
            'n_partikel'      => 'required|integer|min:5|max:500',
            'n_iterasi'       => 'required|integer|min:10|max:1000',
            'early_stop_iter' => 'required|integer|min:5|max:200',
            'w_max'           => 'required|numeric|min:0.5|max:1.0',
            'w_min'           => 'required|numeric|min:0.1|max:0.9',
            'c1'              => 'required|numeric|min:0.5|max:4.0',
            'c2'              => 'required|numeric|min:0.5|max:4.0',
            'base_seed'       => 'required|integer|min:0',
            // Operasional
            'harga_solar'     => 'required|integer|min:1000',
            'tarif_dasar'     => 'required|integer|min:1',
            'bbm_base'        => 'required|numeric|min:0.01|max:1.0',
            'bbm_faktor'      => 'required|numeric|min:0.001|max:0.5',
        ]);

        $psoKeys = ['n_partikel','n_iterasi','early_stop_iter','w_max','w_min','c1','c2','base_seed'];
        $opsKeys = ['harga_solar','tarif_dasar','bbm_base','bbm_faktor'];

        foreach ($psoKeys as $key) {
            Setting::updateOrCreate(
                ['param_group' => 'pso', 'param_key' => $key],
                ['param_value' => (string) $request->input($key)]
            );
        }
        foreach ($opsKeys as $key) {
            Setting::updateOrCreate(
                ['param_group' => 'operasional', 'param_key' => $key],
                ['param_value' => (string) $request->input($key)]
            );
        }

        return back()->with('success', 'Parameter PSO berhasil disimpan. Akan berlaku pada run PSO berikutnya.');
    }
}
