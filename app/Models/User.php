<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';
    
    protected $fillable = ['username', 'password_hash', 'role'];
    
    protected $hidden = ['password_hash'];

    // Beritahu Laravel kolom password ada di 'password_hash'
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // Beritahu Laravel nama kolom password untuk hashing
    public function getAuthIdentifierName(): string
    {
        return 'username';
    }
}