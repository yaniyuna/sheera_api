<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SkenarioPanggilanPalsu;
use Illuminate\Http\Request;

class SkenarioPanggilanPalsuController extends Controller
{
    /**
     * Menampilkan semua skenario panggilan palsu yang aktif.
     */
    public function index()
    {
        // Ambil semua skenario yang statusnya aktif
        $skenarios = SkenarioPanggilanPalsu::where('is_active', true)->get();

        return response()->json($skenarios);
    }
}