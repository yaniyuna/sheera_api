<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; // Untuk upload file

class LaporanController extends Controller
{
    // Menampilkan semua laporan milik user yang sedang login
    // public function index(Request $request)
    // {
    //     $user = $request->user();

    //     // Cek role dari user yang sedang login
    //     if ($user->role === 'admin') {
    //         // Jika user adalah admin, ambil semua laporan dari semua pengguna
    //         // Kita tambahkan 'user' di with() agar tahu siapa yang melapor
    //         $laporans = Laporan::with('user', 'statusLaporan', 'buktiLaporans')
    //                             ->latest() // Urutkan dari yang terbaru
    //                             ->paginate(10); // Gunakan pagination
    //     } else {
    //         // Jika user biasa, ambil hanya laporan miliknya sendiri
    //         $laporans = $user->laporans()
    //                          ->with('statusLaporan', 'buktiLaporans')
    //                          ->latest()
    //                          ->paginate(10);
    //     }

    //     return response()->json($laporans);
    // }

    public function index(Request $request)
    {
        $user = $request->user();

        // Query builder kosong sebagai dasar
        $query = Laporan::query();

        // Logika #1: Jika yang meminta adalah admin
        if ($user->role === 'admin') {
            // Admin bisa melihat semua laporan, dengan data user pelapor
            $query->with('user', 'statusLaporan', 'buktiLaporans');
        } 
        // Logika #2: Jika ada parameter view=community (untuk halaman Community Alert)
        else if ($request->has('view') && $request->view === 'community') {
            // Tampilkan SEMUA laporan dari SEMUA user, TAPI HANYA yang statusnya 'Selesai'
            // Asumsi ID status 'Selesai' adalah 4
            $query->with('user', 'statusLaporan')->where('status_laporan_id', 4);
        } 
        // Logika #3: Default untuk user biasa (melihat laporannya sendiri)
        else {
            $query = $user->laporans()->with('statusLaporan', 'buktiLaporans');
        }

        // Terapkan filter pencarian jika ada (berlaku untuk semua logika di atas)
        if ($request->has('search') && $request->search != '') {
            $query->where('judul_laporan', 'like', '%' . $request->search . '%');
        }
        
        // Terapkan filter status jika ada (biasanya digunakan oleh admin)
        if ($request->has('status_id') && $request->status_id != '') {
            $query->where('status_laporan_id', $request->status_id);
        }

        // Urutkan dari yang terbaru dan terapkan pagination
        $laporans = $query->latest()->paginate(10);

        return response()->json($laporans);
    }

    // Membuat laporan baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul_laporan' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'waktu_kejadian' => 'required|date',
            'bukti.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,mp3|max:20480' // max 20MB
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Buat Laporan, status default 'Baru' (ID=1)
        $laporan = $request->user()->laporans()->create([
            'judul_laporan' => $request->judul_laporan,
            'deskripsi' => $request->deskripsi,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'waktu_kejadian' => $request->waktu_kejadian,
            'status_laporan_id' => 1, // Asumsi ID 1 adalah 'Baru'
        ]);

        // Jika ada file bukti yang di-upload
        if ($request->hasFile('bukti')) {
            foreach ($request->file('bukti') as $file) {
                // Simpan file ke storage/app/public/bukti_laporan
                $path = $file->store('bukti_laporan', 'public');
                $url = Storage::url($path); // Dapatkan URL publik

                // Simpan path ke database
                $laporan->buktiLaporans()->create([
                    'file_url' => $url,
                    'tipe_file' => $file->getClientOriginalExtension()
                ]);
            }
        }
        
        // Agar file bisa diakses dari URL, jalankan: php artisan storage:link

        return response()->json([
            'message' => 'Laporan berhasil dibuat',
            'data' => $laporan->load('buktiLaporans')
        ], 201);
    }

    public function show(Laporan $laporan)
    {
        // Muat relasi agar data bukti dan status ikut terbawa
        $laporan->load('user', 'statusLaporan', 'buktiLaporans');

        // Untuk keamanan, Anda bisa tambahkan pengecekan kepemilikan di sini juga jika diperlukan
        // if (request()->user()->id !== $laporan->user_id && request()->user()->role !== 'admin') {
        //     return response()->json(['message' => 'Akses ditolak.'], 403);
        // }

        return response()->json($laporan);
    }

    public function update(Request $request, Laporan $laporan)
    {
        // 1. Cek Kepemilikan: Pastikan user hanya bisa mengedit laporannya sendiri.
        if ($request->user()->id !== $laporan->user_id) {
            return response()->json(['message' => 'Akses ditolak. Anda bukan pemilik laporan ini.'], 403);
        }

        // 2. Validasi Input: Mirip seperti store, tapi tidak semua wajib diisi.
        $validator = Validator::make($request->all(), [
            'judul_laporan' => 'sometimes|required|string|max:255',
            'deskripsi' => 'sometimes|required|string',
            // Tambahkan field lain jika perlu diedit
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 3. Update data laporan dengan data yang tervalidasi
        $laporan->update($validator->validated());

        // 4. Kirim response
        return response()->json([
            'message' => 'Laporan berhasil diperbarui.',
            'data' => $laporan
        ]);
    }

    public function destroy(Request $request, Laporan $laporan)
    {
        // 1. Cek Kepemilikan: Keamanan nomor satu!
        if ($request->user()->id !== $laporan->user_id) {
            return response()->json(['message' => 'Akses ditolak. Anda bukan pemilik laporan ini.'], 403);
        }

        // 2. Hapus file bukti dari storage (PENTING!)
        // Jika tidak, file akan menumpuk di server meski datanya sudah dihapus.
        foreach ($laporan->buktiLaporans as $bukti) {
            // Hapus '/storage' dari URL untuk mendapatkan path yang benar di folder storage
            $path = str_replace('/storage/', '', $bukti->file_url);
            Storage::disk('public')->delete($path);
        }
        
        // 3. Hapus laporan dari database
        // (Record di 'bukti_laporans' akan ikut terhapus karena kita memakai onDelete('cascade'))
        $laporan->delete();

        // 4. Kirim response
        return response()->json(['message' => 'Laporan berhasil dihapus.']);
    }

    public function updateStatus(Request $request, Laporan $laporan)
    {
        $validator = Validator::make($request->all(), [
            'status_laporan_id' => 'required|integer|exists:status_laporans,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $laporan->status_laporan_id = $request->status_laporan_id;
        $laporan->save();

        return response()->json([
            'message' => 'Status laporan berhasil diperbarui.',
            'data' => $laporan->load('statusLaporan')
        ]);
    }
}