<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Services\KeanggotaanService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KeanggotaanController extends Controller
{
    protected $svc;

    public function __construct(KeanggotaanService $svc)
    {
        $this->svc = $svc;
    }

    // ------------------------------------------------------------------
    // Arsip anggota (pencarian semua status)
    // ------------------------------------------------------------------

    public function index(Request $request)
    {
        $q = Siswa::query();

        $kata = trim((string) $request->input('q'));
        if ($kata !== '') {
            $q->where(function ($w) use ($kata) {
                $w->where('nama', 'like', '%' . $kata . '%')
                  ->orWhere('nisn', 'like', '%' . $kata . '%');
            });
        }

        $status = (string) $request->input('status');
        if (isset(Siswa::statusList()[$status])) {
            $q->where('status', $status);
        } else {
            $status = '';
        }

        $kelas = trim((string) $request->input('kelas'));
        if ($kelas !== '') {
            $q->where('kelas', $kelas);
        }

        $siswa      = $q->orderBy('nama')->paginate(25)->withQueryString();
        $tanggungan = $this->svc->tanggungan($siswa->pluck('id')->all());

        $kelasList = Siswa::query()->whereNotNull('kelas')->where('kelas', '!=', '')
            ->distinct()->orderBy('kelas')->pluck('kelas');

        $ringkas = Siswa::query()->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('petugas.keanggotaan.index', [
            'siswa'      => $siswa,
            'tanggungan' => $tanggungan,
            'kelasList'  => $kelasList,
            'ringkas'    => $ringkas,
            'filter'     => ['q' => $kata, 'status' => $status, 'kelas' => $kelas],
        ]);
    }

    // ------------------------------------------------------------------
    // Kenaikan kelas & kelulusan massal
    // ------------------------------------------------------------------

    public function kenaikan()
    {
        return view('petugas.keanggotaan.kenaikan', [
            'rombel' => $this->svc->rombelAktif(),
            'log'    => $this->svc->logTerakhir(15),
        ]);
    }

    public function naik(Request $request)
    {
        $request->validate([
            'src'    => 'required|array',
            'target' => 'required|array',
        ]);

        $src    = $request->input('src', []);
        $target = $request->input('target', []);
        $pilih  = $request->input('pilih', []);

        $mapping = [];
        foreach ($src as $i => $asal) {
            if (isset($pilih[$i]) && isset($target[$i])) {
                $mapping[$asal] = $target[$i];
            }
        }

        $res = $this->svc->naikKelas($mapping, auth()->id());

        if (!$res['ok']) {
            return back()->with('error', $res['pesan']);
        }

        return back()->with('success', "Kenaikan kelas selesai: {$res['siswa']} siswa dari {$res['rombel']} rombel dipindahkan.");
    }

    public function lulus(Request $request)
    {
        $request->validate([
            'rombel'   => 'required|array|min:1',
            'rombel.*' => 'string',
            'tahun'    => 'required|integer|min:2000|max:2100',
        ]);

        $res = $this->svc->luluskan(
            $request->input('rombel', []),
            (int) $request->input('tahun'),
            $request->has('paksa'),
            auth()->id()
        );

        if (!$res['ok']) {
            return back()->with('error', $res['pesan']);
        }

        $pesan = "Kelulusan selesai: {$res['lulus']} siswa berstatus Lulus/Alumni.";
        $back  = back()->with('success', $pesan);

        if (count($res['ditahan']) > 0) {
            $back->with('ditahan', $res['ditahan']);
        }

        return $back;
    }

    // ------------------------------------------------------------------
    // Penyesuaian individual
    // ------------------------------------------------------------------

    public function ubahStatus(Request $request, Siswa $siswa)
    {
        $request->validate([
            'status' => 'required|in:active,graduated,transferred,inactive',
            'alasan' => 'nullable|string|max:255',
        ]);

        $res = $this->svc->ubahStatus(
            $siswa,
            $request->input('status'),
            $request->input('alasan'),
            $request->has('paksa'),
            auth()->id()
        );

        if (!$res['ok']) {
            $back = back()->with('error', $res['pesan']);

            if (isset($res['tanggungan'])) {
                $back->with('tanggungan_siswa', [
                    'nama' => $siswa->nama,
                    'data' => $res['tanggungan'],
                ]);
            }

            return $back;
        }

        return back()->with('success', 'Status ' . $siswa->nama . ' diubah menjadi ' . Siswa::statusList()[$request->input('status')] . '.');
    }

    public function ubahKelas(Request $request, Siswa $siswa)
    {
        $request->validate([
            'kelas'  => 'required|string|max:100',
            'alasan' => 'nullable|string|max:255',
        ]);

        $res = $this->svc->ubahKelas($siswa, $request->input('kelas'), $request->input('alasan'), auth()->id());

        if (!$res['ok']) {
            return back()->with('error', $res['pesan']);
        }

        return back()->with('success', 'Kelas ' . $siswa->nama . ' diubah menjadi ' . trim($request->input('kelas')) . '.');
    }

    // ------------------------------------------------------------------
    // Surat Keterangan Bebas Pustaka
    // ------------------------------------------------------------------

    public function bebasPustaka(Siswa $siswa)
    {
        $surat = $this->svc->terbitkanBebasPustaka($siswa, auth()->id());

        if ($surat === null) {
            $tg = $this->svc->tanggungan([$siswa->id])[$siswa->id];

            return back()
                ->with('error', 'Surat Bebas Pustaka tidak dapat dibuat: masih ada tanggungan.')
                ->with('tanggungan_siswa', ['nama' => $siswa->nama, 'data' => $tg]);
        }

        $data = [
            'siswa'    => $siswa,
            'surat'    => $surat,
            'cfg'      => config('laporan'),
            'bulan'    => KeanggotaanService::NAMA_BULAN,
        ];

        $logoPath = public_path(config('laporan.logo'));
        $nama     = 'bebas-pustaka-' . $siswa->nisn . '.pdf';

        if (app()->bound('dompdf.wrapper')) {
            $data['logo']     = is_file($logoPath) ? $logoPath : null;
            $data['htmlMode'] = false;

            return app('dompdf.wrapper')
                ->loadView('petugas.keanggotaan.bebas-pustaka', $data)
                ->setPaper('a4', 'portrait')
                ->stream($nama);
        }

        $data['logo']     = is_file($logoPath) ? asset(config('laporan.logo')) : null;
        $data['htmlMode'] = true;

        return view('petugas.keanggotaan.bebas-pustaka', $data);
    }
}