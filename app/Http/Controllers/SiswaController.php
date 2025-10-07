<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SiswaImport;
use Illuminate\Http\Request;
use Exception;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::query();

        // filter pencarian
        if ($request->has('q') && !empty($request->q)) {
            $query->where('nama', 'like', '%' . $request->q . '%')
              ->orWhere('nisn', 'like', '%' . $request->q . '%')
              ->orWhere('kelas', 'like', '%' . $request->q . '%');
        }

        $siswa = $query->orderBy('id', 'desc')->paginate(25);
        $total_siswa = $query->count();
        // agar query pencarian ikut di pagination
        $siswa->appends($request->only('q'));

        return view('admin.siswa.siswa', compact('siswa','total_siswa'));
    }

    public function simpan_siswa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nisn'      => 'required|string|unique:siswa,nisn',
            'nama'    => 'required',
            'kelas' => 'required',
            'jenis_kelamin' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
        ]);
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            Siswa::create([
                'nisn'     => $request->nisn,
                'nama'    => $request->nama,
                'kelas' => $request->kelas,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'status'   => 'Aktif',
            ]);

            DB::commit();

            return redirect()->route('admin.siswa')
                         ->with('toast_success', 'Data berhasil ditambahkan!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function import_excel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new SiswaImport, $request->file('file'));

            // 🔥 Redirect setelah sukses
            return redirect()->route('admin.siswa')
                ->with('toast_success', 'Import siswa berhasil!');
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return redirect()->route('admin.siswa')
                ->with('toast_error', 'Import gagal pada baris: ' . $failures[0]->row() . ' - ' . $failures[0]->errors()[0]);
        }
    }

    public function edit_siswa(Request $request)
    {
        $id_user = $request->id;

        $validator = Validator::make($request->all(), [
            'nisn'     => 'required',
            'nama'    => 'required',
            'kelas' => 'required',
            'jenis_kelamin' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'status'   => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $siswa = Siswa::findOrFail($id_user);

            $data = [
                'nisn'     => $request->nisn,
                'nama'    => $request->nama,
                'kelas' => $request->kelas,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'status'   => $request->status,
            ];

            $siswa->update($data);

            DB::commit();

            return redirect()->route('admin.siswa')
                         ->with('toast_success', 'Data berhasil diperbarui!')
                         ->with('activeTab', 'akun');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                         ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                         ->with('activeTab', 'akun');
        }
    }

    public function hapus_siswa($id)
    {
        $siswa = Siswa::find($id);

        if (!$siswa) {
            return redirect()->back()->with('toast_error', 'Data tidak ditemukan!');
        }

        $siswa->delete();
        return redirect()->route('admin.siswa')
                         ->with('toast_success', 'Data Berhasil Dihapus!');
    }
}
