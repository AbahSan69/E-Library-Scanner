<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;

class AkunController extends Controller
{
    public function index()
    {
        $akun = User::all();
        return view('admin.akun.akun', compact('akun'));
    }

    public function simpan_akun(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'status'   => 'required',
        ]);
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'status'   => $request->status,
                'role_id' => $request->role,
            ]);

            DB::commit();

            return redirect()->route('admin.akun')
                         ->with('toast_success', 'Data berhasil ditambahkan!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function edit_akun(Request $request)
    {
        $id_user = $request->id;

        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'email' => 'required|email|unique:users,email,' . $id_user, // biar email tidak duplikat, kecuali dirinya sendiri
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $user = User::findOrFail($id_user);

            $data = [
                'name'  => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->has('status')) {
                $data['status'] = $request->status;
            }

            $user->update($data);

            DB::commit();

            return redirect()->route('admin.akun')
                         ->with('toast_success', 'Data super admin berhasil diperbarui!')
                         ->with('activeTab', 'akun');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                         ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                         ->with('activeTab', 'akun');
        }
    }

    public function hapus_akun($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('toast_error', 'Data tidak ditemukan!');
        }

        $user->delete();
        return redirect()->route('admin.akun')
                         ->with('toast_success', 'Data Berhasil Dihapus!');
    }
}
