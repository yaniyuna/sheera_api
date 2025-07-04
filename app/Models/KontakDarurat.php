<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KontakDarurat extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'nama_kontak', 'nomor_telepon'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}