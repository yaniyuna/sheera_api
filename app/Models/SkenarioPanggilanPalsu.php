<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkenarioPanggilanPalsu extends Model
{
    use HasFactory;
    protected $fillable = [
        'judul_skenario',
        'nama_penelepon',
        'audio_url',
        'teks_skrip',
        'is_active'
    ];
}