<?php

namespace App\Controllers\PenetapanKonteks;

use App\Models\AnalisisSwotPestleModel;
use App\Models\AnalisisPeriodeModel;
use App\Models\KonteksModel;

class AnalisisSwotPestleController extends BaseContextController
{
    protected $analisisModel;
    protected $periodeModel;
    protected $konteksModel;

    public function __construct()
    {
        $this->analisisModel = new AnalisisSwotPestleModel();
        $this->periodeModel  = new AnalisisPeriodeModel();
        $this->konteksModel  = new KonteksModel();
    }

    /**
     * Menentukan apakah user boleh mengelola analisis.
     * Hanya admin dan operator.
     */
    private function canManage(): bool
    {
        return in_array(session('user_role'), ['admin', 'operator'], true);
    }

    
/**
 * Memastikan konteks ada dan user memiliki akses.
 */
private function getAuthorizedContext(int $idKonteks): ?array
{
    // Ambil data konteks terlebih dahulu.
    $konteks = $this->konteksModel
        ->where('id_konteks', $idKonteks)
        ->first();

    // Catat informasi untuk debugging tanpa menghentikan proses.
    log_message('debug', 'SWOT SESSION CHECK: ' . json_encode([
        'id_konteks'         => $idKonteks,
        'konteks_ditemukan'  => (bool) $konteks,
        'user_role'          => session('user_role'),
        'user_id_tim'        => session('id_tim'),
        'user_tim'           => session('user_tim'),
        'user_session'       => session('user'),
    ]));

    if (!$konteks) {
        return null;
    }

    $role = session('user_role');
    $user = session('user') ?? [];

    // Admin dapat mengakses semua konteks.
    if ($role === 'admin') {
        return $konteks;
    }

    // Gunakan daftar tim dari session KERIS.
    $userTim = session('user_tim')
        ?? ($user['tim'] ?? []);

    if (empty($userTim) && session('id_tim')) {
        $userTim = [session('id_tim')];
    }

    $userTim = array_map('strval', (array) $userTim);

    // User non-admin hanya boleh mengakses konteks timnya.
    if (
        in_array((string) $konteks['id_tim'], $userTim, true)
        && in_array($role, ['operator', 'ketua', 'pimpinan'], true)
    ) {
        return $konteks;
    }

    return null;
}


    /**
     * Memvalidasi periode analisis.
     * Triwulan berakhir pada Maret, Juni, September, dan Desember.
     * Periode lampau read-only kecuali dibuka kembali oleh admin.
     */
    private function isPeriodEditable(
        int $idKonteks,
        int $tahun,
        int $triwulan
    ): bool {
        $now = new \DateTimeImmutable('now');

        $endMonth = $triwulan * 3;

        $endDate = new \DateTimeImmutable(
            sprintf('%04d-%02d-01 23:59:59', $tahun, $endMonth)
        );

        $endDate = $endDate->modify('last day of this month');

        // Periode yang belum berakhir masih dapat dikelola.
        if ($now <= $endDate) {
            return true;
        }

        // Periode lampau hanya dapat diedit jika dibuka kembali.
        $periode = $this->periodeModel->getPeriode(
            $idKonteks,
            $tahun,
            $triwulan
        );

        return (bool) ($periode['dibuka_kembali'] ?? false);
    }

    /**
     * Mengambil poin analisis berdasarkan konteks,
     * jenis, kategori, tahun, dan triwulan.
     */
    public function list()
    {
        $idKonteks = (int) $this->request->getGet('id_konteks');
        $jenis     = strtoupper(trim((string) $this->request->getGet('jenis')));
        $kategori  = strtoupper(trim((string) $this->request->getGet('kategori')));
        $tahun     = (int) $this->request->getGet('tahun');
        $triwulan  = (int) $this->request->getGet('triwulan');

        $konteks = $this->getAuthorizedContext($idKonteks);

        if (!$konteks) {
            return $this->forbidden(
                'Konteks tidak ditemukan atau tidak dapat diakses.'
            );
        }

        if (!$this->isValidCategory($jenis, $kategori)) {
            return $this->invalid('Jenis atau kategori analisis tidak valid.');
        }

        if (
            $tahun !== (int) $konteks['tahun']
            || $triwulan < 1
            || $triwulan > 4
        ) {
            return $this->invalid('Tahun atau triwulan tidak valid.');
        }

        $data = $this->analisisModel
            ->where('id_konteks', $idKonteks)
            ->where('jenis_analisis', $jenis)
            ->where('kategori', $kategori)
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->orderBy('id_analisis', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data,
            'periode' => [
                'tahun'    => $tahun,
                'triwulan' => $triwulan,
                'editable' => $this->canManage()
                    && $this->isPeriodEditable(
                        $idKonteks,
                        $tahun,
                        $triwulan
                    ),
            ],
        ]);
    }


    public function store()
    {
        if (!$this->canManage()) {
            return $this->forbidden();
        }

        $idKonteks = (int) $this->request->getPost('id_konteks');
        $jenis     = strtoupper(trim((string) $this->request->getPost('jenis_analisis')));
        $kategori  = strtoupper(trim((string) $this->request->getPost('kategori')));
        $tahun     = (int) $this->request->getPost('tahun');
        $triwulan  = (int) $this->request->getPost('triwulan');
        $uraian    = trim((string) $this->request->getPost('uraian'));

        $konteks = $this->getAuthorizedContext($idKonteks);

        if (!$konteks) {
            return $this->forbidden('Konteks tidak ditemukan atau tidak dapat diakses.');
        }

        if (!$this->isValidCategory($jenis, $kategori)) {
            return $this->invalid('Jenis atau kategori tidak valid.');
        }

        if ($tahun !== (int) $konteks['tahun']) {
            return $this->invalid('Tahun tidak sesuai dengan konteks.');
        }

        if ($triwulan < 1 || $triwulan > 4) {
            return $this->invalid('Triwulan tidak valid.');
        }

        if ($uraian === '') {
            return $this->invalid('Uraian analisis wajib diisi.');
        }

        if (!$this->isPeriodEditable($idKonteks, $tahun, $triwulan)) {
            return $this->locked();
        }

        $id = $this->analisisModel->insert([
            'id_konteks'     => $idKonteks,
            'jenis_analisis' => $jenis,
            'kategori'       => $kategori,
            'tahun'          => $tahun,
            'triwulan'       => $triwulan,
            'uraian'         => $uraian,
        ]);

        if (!$id) {
            return $this->invalid('Gagal menyimpan analisis.');
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Poin analisis berhasil ditambahkan.',
            'id'      => $id,
        ]);
    }


    public function update($id)
    {
        if (!$this->canManage()) {
            return $this->forbidden();
        }

        $row = $this->analisisModel->find((int) $id);

        if (!$row || !$this->getAuthorizedContext((int) $row['id_konteks'])) {
            return $this->forbidden('Data tidak ditemukan atau tidak dapat diakses.');
        }

        $tahun    = (int) $row['tahun'];
        $triwulan = (int) $row['triwulan'];

        if (!$this->isPeriodEditable((int) $row['id_konteks'], $tahun, $triwulan)) {
            return $this->locked();
        }

        $uraian = trim((string) $this->request->getPost('uraian'));

        if ($uraian === '') {
            return $this->invalid('Uraian analisis wajib diisi.');
        }

        $this->analisisModel->update((int) $id, [
            'uraian' => $uraian,
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Poin analisis berhasil diperbarui.',
        ]);
    }


    public function delete($id)
    {
        if (!$this->canManage()) {
            return $this->forbidden();
        }

        $row = $this->analisisModel->find((int) $id);

        if (!$row || !$this->getAuthorizedContext((int) $row['id_konteks'])) {
            return $this->forbidden('Data tidak ditemukan atau tidak dapat diakses.');
        }

        if (
            !$this->isPeriodEditable(
                (int) $row['id_konteks'],
                (int) $row['tahun'],
                (int) $row['triwulan']
            )
        ) {
            return $this->locked();
        }

        $this->analisisModel->delete((int) $id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Poin analisis berhasil dihapus.',
        ]);
    }

    /**
     * Validasi daftar kategori yang diperbolehkan.
     */
    private function isValidCategory(string $jenis, string $kategori): bool
    {
        $categories = [
            'SWOT'   => ['S', 'W', 'O', 'T'],
            'PESTLE' => ['P', 'E', 'S', 'T', 'L', 'ENV'],
        ];

        return isset($categories[$jenis])
            && in_array($kategori, $categories[$jenis], true);
    }

    private function forbidden(string $message = 'Akses ditolak.')
    {
        return $this->response->setStatusCode(403)->setJSON([
            'status'  => 'error',
            'message' => $message,
        ]);
    }

    private function invalid(string $message)
    {
        return $this->response->setStatusCode(422)->setJSON([
            'status'  => 'error',
            'message' => $message,
        ]);
    }

    private function locked()
    {
        return $this->response->setStatusCode(423)->setJSON([
            'status'  => 'error',
            'message' => 'Periode sudah terkunci dan hanya dapat diedit setelah dibuka kembali oleh admin.',
        ]);
    }

    
/**
 * Membuka kembali periode SWOT/PESTLE yang sudah terkunci.
 *
 * Hak akses: admin saja.
 * Alasan pembukaan wajib dicatat untuk kebutuhan dokumentasi.
 */
public function bukaPeriode()
{
    if (session('user_role') !== 'admin') {
        return $this->forbidden(
            'Hanya admin yang dapat membuka kembali periode.'
        );
    }

    $idKonteks = (int) $this->request->getPost('id_konteks');
    $tahun     = (int) $this->request->getPost('tahun');
    $triwulan  = (int) $this->request->getPost('triwulan');
    $alasan    = trim((string) $this->request->getPost('alasan_buka'));

    $konteks = $this->konteksModel->find($idKonteks);

    if (!$konteks) {
        return $this->invalid('Konteks tidak ditemukan.');
    }

    if ($tahun !== (int) $konteks['tahun']) {
        return $this->invalid('Tahun tidak sesuai dengan konteks.');
    }

    if ($triwulan < 1 || $triwulan > 4) {
        return $this->invalid('Triwulan harus bernilai 1 sampai 4.');
    }

    if ($alasan === '') {
        return $this->invalid(
            'Alasan pembukaan kembali periode wajib diisi.'
        );
    }

    $periode = $this->periodeModel->getPeriode(
        $idKonteks,
        $tahun,
        $triwulan
    );

    $user = session('user') ?? [];

    // Sesuaikan dengan struktur sesi autentikasi KERIS.
    $idAdmin = $user['id']
        ?? session('user_id')
        ?? session('id_user')
        ?? null;

    $data = [
        'dibuka_kembali' => true,
        'dibuka_oleh'    => $idAdmin ? (int) $idAdmin : null,
        'dibuka_pada'    => date('Y-m-d H:i:s'),
        'alasan_buka'    => $alasan,
    ];

    if ($periode) {
        $this->periodeModel->update(
            (int) $periode['id_periode'],
            $data
        );
    } else {
        $this->periodeModel->insert(array_merge($data, [
            'id_konteks' => $idKonteks,
            'tahun'      => $tahun,
            'triwulan'   => $triwulan,
        ]));
    }

    return $this->response->setJSON([
        'status'  => 'success',
        'message' => 'Periode berhasil dibuka kembali.',
    ]);
}

/**
 * Mengunci kembali periode yang sebelumnya dibuka admin.
 *
 * Hak akses: admin saja.
 */
public function tutupPeriode()
{
    if (session('user_role') !== 'admin') {
        return $this->forbidden(
            'Hanya admin yang dapat mengunci kembali periode.'
        );
    }

    $idKonteks = (int) $this->request->getPost('id_konteks');
    $tahun     = (int) $this->request->getPost('tahun');
    $triwulan  = (int) $this->request->getPost('triwulan');

    $konteks = $this->konteksModel->find($idKonteks);

    if (!$konteks || $tahun !== (int) $konteks['tahun']) {
        return $this->invalid(
            'Konteks atau tahun tidak valid.'
        );
    }

    if ($triwulan < 1 || $triwulan > 4) {
        return $this->invalid('Triwulan tidak valid.');
    }

    $periode = $this->periodeModel->getPeriode(
        $idKonteks,
        $tahun,
        $triwulan
    );

    if (!$periode) {
        return $this->invalid(
            'Pengaturan periode tidak ditemukan.'
        );
    }

    $this->periodeModel->update(
        (int) $periode['id_periode'],
        ['dibuka_kembali' => false]
    );

    return $this->response->setJSON([
        'status'  => 'success',
        'message' => 'Periode berhasil dikunci kembali.',
    ]);
}

}
