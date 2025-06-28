<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\KontakDarurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KontakDaruratController extends Controller
{
    /**
     * Menampilkan daftar kontak darurat milik user yang sedang login.
     */
    public function index(Request $request)
    {
        $kontak = $request->user()->kontakDarurats()->latest()->get();
        return response()->json($kontak);
    }

    /**
     * Menyimpan kontak darurat baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kontak' => 'required|string|max:255',
            'nomor_telepon' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kontak = $request->user()->kontakDarurats()->create($validator->validated());

        return response()->json([
            'message' => 'Kontak darurat berhasil ditambahkan.',
            'data' => $kontak
        ], 201);
    }

    /**
     * Menampilkan detail satu kontak darurat.
     */
    public function show(Request $request, KontakDarurat $kontakDarurat)
    {
        // Cek Kepemilikan!
        if ($request->user()->id !== $kontakDarurat->user_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        return response()->json($kontakDarurat);
    }

    /**
     * Mengupdate kontak darurat yang sudah ada.
     */
    public function update(Request $request, KontakDarurat $kontakDarurat)
    {
        // 1. Cek Kepemilikan: Pastikan user hanya bisa mengedit kontaknya sendiri.
        if ($request->user()->id !== $kontakDarurat->user_id) {
            return response()->json(['message' => 'Akses ditolak. Anda bukan pemilik kontak ini.'], 403);
        }

        // 2. Validasi Input
        $validator = Validator::make($request->all(), [
            'nama_kontak' => 'sometimes|required|string|max:255',
            'nomor_telepon' => 'sometimes|required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 3. Update data kontak
        $kontakDarurat->update($validator->validated());

        // 4. Kirim response
        return response()->json([
            'message' => 'Kontak darurat berhasil diperbarui.',
            'data' => $kontakDarurat
        ]);
    }

    /**
     * Menghapus kontak darurat.
     */
    public function destroy(Request $request, KontakDarurat $kontakDarurat)
    {
        // 1. Cek Kepemilikan: Keamanan nomor satu!
        if ($request->user()->id !== $kontakDarurat->user_id) {
            return response()->json(['message' => 'Akses ditolak. Anda bukan pemilik kontak ini.'], 403);
        }

        // 2. Hapus kontak dari database
        $kontakDarurat->delete();

        // 3. Kirim response
        return response()->json(['message' => 'Kontak darurat berhasil dihapus.']);
    }
}