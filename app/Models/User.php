<?php
// app/Models/User.php
// Mirror dari db/models.py → class User
// Kolom password_hash (bukan 'password') agar sinkron dengan Streamlit.

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    // Kolom password_hash — override default 'password' Laravel
    protected $authPasswordName = 'password_hash';

    protected $fillable = [
        'username', 'password_hash', 'role',
    ];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'created_at' => 'datetime',
        
    ];

    // Laravel Auth butuh method getAuthPassword() mengarah ke field yang benar
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // Username sebagai identifier login (bukan email)
    public function getAuthIdentifierName(): string
    {
        return 'username';
    }
}
