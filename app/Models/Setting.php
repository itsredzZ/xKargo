<?php
// app/Models/Setting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // Tabel otomatis dikenali sebagai 'settings' oleh Laravel, tapi boleh ditulis untuk kepastian
    protected $table = 'settings';
    
    // HAPUS public $timestamps = false;
    // HAPUS const UPDATED_AT = ...
    // HAPUS const CREATED_AT = ...
    // Karena Laravel sekarang sudah mengurus semuanya secara otomatis!

    protected $fillable = ['param_group', 'param_key', 'param_value'];

    // Helper: ambil nilai setting by group + key, cast ke float jika perlu
    public static function getValue(string $group, string $key, $default = null)
    {
        $s = static::where('param_group', $group)
                   ->where('param_key', $key)
                   ->first();
        return $s ? $s->param_value : $default;
    }

    // Helper: ambil semua PSO settings sebagai array [key => value]
    public static function getPsoParams(): array
    {
        return static::where('param_group', 'pso')
                     ->pluck('param_value', 'param_key')
                     ->toArray();
    }

    // Helper: ambil semua operasional settings
    public static function getOperasionalParams(): array
    {
        return static::where('param_group', 'operasional')
                     ->pluck('param_value', 'param_key')
                     ->toArray();
    }
}