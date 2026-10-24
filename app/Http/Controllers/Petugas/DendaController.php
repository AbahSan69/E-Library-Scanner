<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Denda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DendaController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:unpaid,' . Denda::PAID . ',' . Denda::WAIVED,
        ]);

        $denda = Denda::with(['siswa', 'detail.buku', 'penerima'])
            ->when($request->status, function ($q, $s) {
                return $q->where('status', $s);
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('petugas.denda.index', compact('denda'));
    }

    public function bayar(Denda $denda)
    {
        DB::transaction(function () use ($denda) {
            $d = Denda::lockForUpdate()->findOrFail($denda->id);

            abort_unless(
                $d->bisaDiproses(),
                422,
                'Denda ini tidak bisa dibayar (sudah diproses atau buku belum dikembalikan).'
            );

            $d->update([
                'status'        => Denda::PAID,
                'dibayar_pada'  => now(),
                'diterima_oleh' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Pembayaran denda dicatat.');
    }

    public function dispensasi(Request $request, Denda $denda)
    {
        $data = $request->validate([
            'alasan_dispensasi' => 'required|string|min:5|max:500',
        ]);

        DB::transaction(function () use ($denda, $data) {
            $d = Denda::lockForUpdate()->findOrFail($denda->id);

            abort_unless(
                $d->bisaDiproses(),
                422,
                'Denda ini tidak bisa didispensasi (sudah diproses atau buku belum dikembalikan).'
            );

            $d->update([
                'status'            => Denda::WAIVED,
                'alasan_dispensasi' => $data['alasan_dispensasi'],
                'dispensasi_oleh'   => auth()->id(),
            ]);
        });

        return back()->with('success', 'Denda dihapus dengan dispensasi.');
    }
}