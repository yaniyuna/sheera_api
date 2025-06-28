<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuktiLaporan extends Model
{
    use HasFactory;
    protected $fillable = ['laporan_id', 'file_url', 'tipe_file'];

    public function laporan() {
        return $this->belongsTo(Laporan::class);
    }
}