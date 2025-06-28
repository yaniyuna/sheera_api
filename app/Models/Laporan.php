<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status_laporan_id',
        'judul_laporan',
        'deskripsi',
        'latitude',
        'longitude',
        'waktu_kejadian',
    ];

    // Relasi: Laporan ini milik satu User
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Relasi: Laporan ini punya satu status
    public function statusLaporan() {
        return $this->belongsTo(StatusLaporan::class);
    }

    // Relasi: Laporan ini bisa punya banyak bukti
    public function buktiLaporans() {
        return $this->hasMany(BuktiLaporan::class);
    }
}