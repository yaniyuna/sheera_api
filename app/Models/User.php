<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- PENTING UNTUK API

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'password',
        'nomor_telepon',
        'fcm_token',
        'role',
    ];

    protected $hidden = [ 'password', 'remember_token', ];
    protected $casts = [ 'email_verified_at' => 'datetime', ];

    // Relasi: Satu user bisa punya banyak laporan
    public function laporans() {
        return $this->hasMany(Laporan::class);
    }

    // Relasi: Satu user bisa punya banyak kontak darurat
    public function kontakDarurats() {
        return $this->hasMany(KontakDarurat::class);
    }
}