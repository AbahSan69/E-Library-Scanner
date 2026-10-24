<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class PengaturanDendaController extends Controller
{
    public function index()
    {
        return view('admin.denda.pengaturan', [
            'setting'   => SystemSetting::current(),
            'hariLibur' => HariLibur::orderBy('tanggal', 'desc')->paginate(15),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'fine_rate_per_day' => 'required|integer|min:0',
            'grace_period_days' => 'required|integer|min:0',
        ]);

        $data['exclude_saturday'] = $request->boolean('exclude_saturday');
        $data['exclude_sunday']   = $request->boolean('exclude_sunday');
        $data['exclude_holidays'] = $request->boolean('exclude_holidays');

        $setting = SystemSetting::query()->first();

        if (!$setting) {
            $setting = new SystemSetting();
        }

        $setting->fill($data)->save();

        return back()->with('success', 'Pengaturan denda disimpan.');
    }

    public function tambahLibur(Request $request)
    {
        $data = $request->validate([
            'tanggal'    => 'required|date|unique:hari_libur,tanggal',
            'keterangan' => 'nullable|string|max:255',
        ]);

        HariLibur::create($data);

        return back()->with('success', 'Hari libur ditambahkan.');
    }

    public function hapusLibur(HariLibur $hariLibur)
    {
        $hariLibur->delete();

        return back()->with('success', 'Hari libur dihapus.');
    }
}