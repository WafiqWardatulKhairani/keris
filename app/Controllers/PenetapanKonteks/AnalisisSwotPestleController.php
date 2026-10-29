<?php

namespace App\Controllers\PenetapanKonteks;

use App\Models\AnalisisSwotPestleModel;
use App\Models\AnalisisPeriodeModel;
use App\Models\KonteksModel;
use App\Models\TimKerjaModel;

class AnalisisSwotPestleController extends BaseContextController
{
    protected $analisisModel;
    protected $periodeModel;
    protected $konteksModel;
    protected $timModel;

    public function __construct()
    {
        $this->analisisModel = new AnalisisSwotPestleModel();
        $this->periodeModel  = new AnalisisPeriodeModel();
        $this->konteksModel  = new KonteksModel();
        $this->timModel      = new TimKerjaModel();
    }

    private function canManage(): bool
    {
        return in_array(
            session('user_role'),
            ['admin', 'operator'],
            true
        );
    }

    /**
     * Memvalidasi tim yang dipilih pada global context.
     */
    private function getAuthorizedTeam(int $idTim): ?array
    {
        if ($idTim <= 0) {
            return null;
        }

        $tim = $this->timModel->find($idTim);

        if (!$tim || ($tim['nama_tim'] ?? '') === 'Bagian Umum') {
            return null;
        }

        $role = session('user_role');

        if ($role === 'admin') {
            return $tim;
        }

        if (!in_array($role, ['operator', 'ketua', 'pimpinan'], true)) {
            return null;
        }

        $userTim = session('user_tim') ?? [];
        $user = session('user') ?? [];

        if (empty($userTim) && !empty($user['tim'])) {
            $userTim = $user['tim'];
        }

        if (empty($userTim) && session('id_tim')) {
            $userTim = [session('id_tim')];
        }

        $userTim = array_map('strval', (array) $userTim);

        return in_array((string) $idTim, $userTim, true)
            ? $tim
            : null;
    }

    /**
     * Menentukan tim dan tahun dari request/global context.
     * Tim yang diminta harus sama dengan tim aktif.
     */
    private function getRequestedScope(bool $post = false): ?array
    {
        $idTim = (int) (
            $post
                ? $this->request->getPost('id_tim')
                : $this->request->getGet('id_tim')
        );

        $tahun = (int) (
            $post
                ? $this->request->getPost('tahun')
                : $this->request->getGet('tahun')
        );

        $globalTim = (int) session('global_id_tim');
        $globalTahun = (int) session('global_tahun');

        if ($idTim <= 0) {
            $idTim = $globalTim ?: (int) session('id_tim');
        }

        if ($tahun <= 0) {
            $tahun = $globalTahun ?: (int) date('Y');
        }

        if (
            $globalTim > 0 &&
            $idTim !== $globalTim
        ) {
            return null;
        }

        if (
            $globalTahun > 0 &&
            $tahun !== $globalTahun
        ) {
            return null;
        }

        if (!$this->getAuthorizedTeam($idTim)) {
            return null;
        }

        return [
            'id_tim' => $idTim,
            'tahun'  => $tahun,
        ];
    }

    private function isValidCategory(
        string $jenis,
        string $kategori
    ): bool {
        $categories = [
            'SWOT'   => ['S', 'W', 'O', 'T'],
            'PESTLE' => ['P', 'E', 'S', 'T', 'L', 'ENV'],
        ];

        return isset($categories[$jenis])
            && in_array($kategori, $categories[$jenis], true);
    }

    private function isValidQuarter(int $triwulan): bool
    {
        return $triwulan >= 1 && $triwulan <= 4;
    }

    /**
     * Tetap mengisi id_konteks untuk kompatibilitas dengan
     * skema data lama apabila kolom tersebut wajib diisi.
     *
     * Data analisis tetap dibaca berdasarkan id_tim, tahun,
     * dan triwulan, bukan berdasarkan id_konteks.
     */
    private function getLegacyContextId(
        int $idTim,
        int $tahun
    ): ?int {
        $idKonteksAktif = (int) session('id_konteks_aktif');

        if ($idKonteksAktif > 0) {
            $konteks = $this->konteksModel
                ->where('id_konteks', $idKonteksAktif)
                ->where('id_tim', $idTim)
                ->where('tahun', $tahun)
                ->first();

            if ($konteks) {
                return (int) $konteks['id_konteks'];
            }
        }

        $konteks = $this->konteksModel
            ->where('id_tim', $idTim)
            ->where('tahun', $tahun)
            ->orderBy('id_konteks', 'ASC')
            ->first();

        return $konteks
            ? (int) $konteks['id_konteks']
            : null;
    }

    private function isPeriodEditable(
        int $idTim,
        int $tahun,
        int $triwulan
    ): bool {
        $endMonth = $triwulan * 3;

        $endDate = new \DateTimeImmutable(
            sprintf('%04d-%02d-01', $tahun, $endMonth)
        );

        $endDate = $endDate
            ->modify('last day of this month')
            ->setTime(23, 59, 59);

        if (new \DateTimeImmutable('now') <= $endDate) {
            return true;
        }

        $periode = $this->periodeModel->getPeriode(
            $idTim,
            $tahun,
            $triwulan
        );

        return (bool) ($periode['dibuka_kembali'] ?? false);
    }

    private function getPeriodStatus(
        int $idTim,
        int $tahun,
        int $triwulan
    ): array {
        $endMonth = $triwulan * 3;

        $endDate = new \DateTimeImmutable(
            sprintf('%04d-%02d-01', $tahun, $endMonth)
        );

        $endDate = $endDate
            ->modify('last day of this month')
            ->setTime(23, 59, 59);

        $isEnded = new \DateTimeImmutable('now') > $endDate;

        $periode = $this->periodeModel->getPeriode(
            $idTim,
            $tahun,
            $triwulan
        );

        $isReopened = (bool) ($periode['dibuka_kembali'] ?? false);

        return [
            'sudah_berakhir' => $isEnded,
            'dibuka_kembali' => $isReopened,
            'terkunci'       => $isEnded && !$isReopened,
        ];
    }

    public function list()
    {
        $scope = $this->getRequestedScope();

        if (!$scope) {
            return $this->forbidden(
                'Tim kerja atau tahun tidak dapat diakses.'
            );
        }

        $jenis = strtoupper(trim(
            (string) $this->request->getGet('jenis')
        ));

        $kategori = strtoupper(trim(
            (string) $this->request->getGet('kategori')
        ));

        $tahun = $scope['tahun'];
        $idTim = $scope['id_tim'];
        $triwulan = (int) $this->request->getGet('triwulan');

        if (
            !$this->isValidCategory($jenis, $kategori) ||
            !$this->isValidQuarter($triwulan)
        ) {
            return $this->invalid(
                'Jenis, kategori, atau triwulan tidak valid.'
            );
        }

        $data = $this->analisisModel
            ->where('id_tim', $idTim)
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
                'tahun'          => $tahun,
                'triwulan'       => $triwulan,
                'editable'       => $this->canManage()
                    && $this->isPeriodEditable(
                        $idTim,
                        $tahun,
                        $triwulan
                    ),
                'status_periode' => $this->getPeriodStatus(
                    $idTim,
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

        $scope = $this->getRequestedScope(true);

        if (!$scope) {
            return $this->forbidden(
                'Tim kerja atau tahun tidak dapat diakses.'
            );
        }

        $idTim = $scope['id_tim'];
        $tahun = $scope['tahun'];

        $jenis = strtoupper(trim(
            (string) $this->request->getPost('jenis_analisis')
        ));

        $kategori = strtoupper(trim(
            (string) $this->request->getPost('kategori')
        ));

        $triwulan = (int) $this->request->getPost('triwulan');
        $uraian = trim((string) $this->request->getPost('uraian'));

        if (!$this->isValidCategory($jenis, $kategori)) {
            return $this->invalid('Jenis atau kategori tidak valid.');
        }

        if (!$this->isValidQuarter($triwulan)) {
            return $this->invalid('Triwulan tidak valid.');
        }

        if ($uraian === '') {
            return $this->invalid('Uraian analisis wajib diisi.');
        }

        if (!$this->isPeriodEditable($idTim, $tahun, $triwulan)) {
            return $this->locked();
        }

        $idKonteks = $this->getLegacyContextId($idTim, $tahun);

        if (!$idKonteks) {
            return $this->invalid(
                'Konteks untuk tim dan tahun ini belum tersedia. ' .
                'Data tidak disimpan.'
            );
        }

        $id = $this->analisisModel->insert([
            'id_konteks'     => $idKonteks,
            'id_tim'         => $idTim,
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

        if (!$row) {
            return $this->forbidden(
                'Data analisis tidak ditemukan.'
            );
        }

        $scope = $this->getRequestedScope(true);

        if (
            !$scope ||
            (int) $row['id_tim'] !== $scope['id_tim'] ||
            (int) $row['tahun'] !== $scope['tahun']
        ) {
            return $this->forbidden(
                'Data analisis bukan milik tim aktif.'
            );
        }

        if (!$this->isPeriodEditable(
            (int) $row['id_tim'],
            (int) $row['tahun'],
            (int) $row['triwulan']
        )) {
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

        if (!$row) {
            return $this->forbidden(
                'Data analisis tidak ditemukan.'
            );
        }

        $scope = $this->getRequestedScope(true);

        if (
            !$scope ||
            (int) $row['id_tim'] !== $scope['id_tim'] ||
            (int) $row['tahun'] !== $scope['tahun']
        ) {
            return $this->forbidden(
                'Data analisis bukan milik tim aktif.'
            );
        }

        if (!$this->isPeriodEditable(
            (int) $row['id_tim'],
            (int) $row['tahun'],
            (int) $row['triwulan']
        )) {
            return $this->locked();
        }

        $this->analisisModel->delete((int) $id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Poin analisis berhasil dihapus.',
        ]);
    }

    public function bukaPeriode()
    {
        if (session('user_role') !== 'admin') {
            return $this->forbidden(
                'Hanya admin yang dapat membuka kembali periode.'
            );
        }

        $scope = $this->getRequestedScope(true);

        if (!$scope) {
            return $this->forbidden(
                'Tim kerja atau tahun tidak dapat diakses.'
            );
        }

        $idTim = $scope['id_tim'];
        $tahun = $scope['tahun'];
        $triwulan = (int) $this->request->getPost('triwulan');
        $alasan = trim(
            (string) $this->request->getPost('alasan_buka')
        );

        if (!$this->isValidQuarter($triwulan)) {
            return $this->invalid('Triwulan harus bernilai 1 sampai 4.');
        }

        if ($alasan === '') {
            return $this->invalid(
                'Alasan pembukaan kembali periode wajib diisi.'
            );
        }

        $idKonteks = $this->getLegacyContextId($idTim, $tahun);

        if (!$idKonteks) {
            return $this->invalid(
                'Konteks untuk tim dan tahun ini belum tersedia.'
            );
        }

        $periode = $this->periodeModel->getPeriode(
            $idTim,
            $tahun,
            $triwulan
        );

        $user = session('user') ?? [];
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
                'id_tim'     => $idTim,
                'tahun'      => $tahun,
                'triwulan'   => $triwulan,
            ]));
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Periode berhasil dibuka kembali.',
        ]);
    }

    public function tutupPeriode()
    {
        if (session('user_role') !== 'admin') {
            return $this->forbidden(
                'Hanya admin yang dapat mengunci kembali periode.'
            );
        }

        $scope = $this->getRequestedScope(true);

        if (!$scope) {
            return $this->forbidden(
                'Tim kerja atau tahun tidak dapat diakses.'
            );
        }

        $idTim = $scope['id_tim'];
        $tahun = $scope['tahun'];
        $triwulan = (int) $this->request->getPost('triwulan');

        if (!$this->isValidQuarter($triwulan)) {
            return $this->invalid('Triwulan tidak valid.');
        }

        $periode = $this->periodeModel->getPeriode(
            $idTim,
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
            'message' =>
                'Periode sudah terkunci dan hanya dapat diedit ' .
                'setelah dibuka kembali oleh admin.',
        ]);
    }
}
