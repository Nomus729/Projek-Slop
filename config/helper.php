<?php
/**
 * Helper dan Fungsi Inti SPK (SAW & Rule-Based Engine)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek status autentikasi pengguna
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Proteksi rute halaman yang membutuhkan login
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Mendapatkan data pengguna yang sedang login
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? '',
        'nama_lengkap' => $_SESSION['nama_lengkap'] ?? 'Pengguna',
        'role' => $_SESSION['role'] ?? 'hrd'
    ];
}

/**
 * Menetapkan pesan flash sesi
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // success, warning, danger, info
        'message' => $message
    ];
}

/**
 * Mengambil dan menghapus pesan flash sesi
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Sanitasi string input
 */
function sanitize($data): string {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Evaluasi Logika Aturan Inferensi (Rule-Based Engine)
 * Sesuai Dokumen Spesifikasi Sistem:
 * - Rule 1: C2 < 70 -> Pelatihan Peningkatan Kompetensi Teknis & Manajemen Target Kerja
 * - Rule 2: C3 < 70 -> Pelatihan Refreshment K3 Industri & Kepatuhan SOP Kerja
 * - Rule 3: C1 < 70 -> Sosialisasi Kedisiplinan Kerja & Pelatihan Manajemen Waktu
 * - Rule 4: C4 < 70 -> Pelatihan Komunikasi Efektif & Team Building
 * - Rule 5: V < 0.60 ATAU > 2 kriteria < 70 -> Program Pembinaan Intensif (Performance Improvement Plan / PIP)
 * - Rule 6: V >= 0.85 DAN seluruh kriteria >= 75 -> Dipertimbangkan untuk Program Talenta Pemimpin (Leadership Development Program) / Promosi
 */
function evaluateRuleBased(float $c1, float $c2, float $c3, float $c4, float $v): array {
    $under70Count = 0;
    if ($c1 < 70) $under70Count++;
    if ($c2 < 70) $under70Count++;
    if ($c3 < 70) $under70Count++;
    if ($c4 < 70) $under70Count++;

    $triggeredRules = [];

    // Rule 6: Performa Unggul
    if ($v >= 0.85 && $c1 >= 75 && $c2 >= 75 && $c3 >= 75 && $c4 >= 75) {
        return [
            'status' => 'Unggul',
            'badge_color' => 'teal',
            'rekomendasi' => 'Dipertimbangkan untuk Tech Lead / Principal / Promosi Jabatan',
            'rules' => ['Rule 6 (Performa Unggul): Skor akhir >= 0.85 dan seluruh kriteria >= 75'],
            'keterangan' => 'Kinerja luar biasa secara konsisten pada seluruh indikator proyek.'
        ];
    }

    // Rule 5: Evaluasi Menyeluruh (PIP)
    if ($v < 0.60 || $under70Count > 2) {
        $detail = [];
        if ($c1 < 70) $detail[] = 'Kedisiplinan & Presensi';
        if ($c2 < 70) $detail[] = 'Target KPI & Delivery';
        if ($c3 < 70) $detail[] = 'Kualitas Kode & SOP';
        if ($c4 < 70) $detail[] = 'Kolaborasi Tim';
        
        $reason = ($v < 0.60) ? "Nilai akhir ($v) di bawah 0.60" : "Terdapat $under70Count kriteria bernilai < 70 (" . implode(', ', $detail) . ")";

        return [
            'status' => 'Perlu Pembinaan',
            'badge_color' => 'danger',
            'rekomendasi' => 'Program Pembinaan Kinerja Intensif (Performance Improvement Plan / PIP)',
            'rules' => ['Rule 5 (Evaluasi Menyeluruh): ' . $reason],
            'keterangan' => 'Memerlukan pendampingan intensif dari Tech Lead dan HRD.'
        ];
    }

    // Rules 1 - 4: Evaluasi Kriteria Spesifik
    $specificRecs = [];
    $ruleExplanations = [];

    if ($c2 < 70) {
        $specificRecs[] = 'Pelatihan Software Engineering & Manajemen Delivery Proyek IT';
        $ruleExplanations[] = 'Rule 1: Nilai KPI & Delivery (' . $c2 . ') < 70';
    }
    if ($c3 < 70) {
        $specificRecs[] = 'Workshop Code Quality, IT Security & DevOps CI/CD';
        $ruleExplanations[] = 'Rule 2: Nilai Kualitas Kode & SOP (' . $c3 . ') < 70';
    }
    if ($c1 < 70) {
        $specificRecs[] = 'Pelatihan Manajemen Waktu & Produktivitas Tim Agile';
        $ruleExplanations[] = 'Rule 3: Nilai Presensi & Disiplin (' . $c1 . ') < 70';
    }
    if ($c4 < 70) {
        $specificRecs[] = 'Pelatihan Komunikasi Efektif & Kolaborasi Tim Developer';
        $ruleExplanations[] = 'Rule 4: Nilai Kolaborasi & Tim (' . $c4 . ') < 70';
    }

    if (!empty($specificRecs)) {
        return [
            'status' => 'Perlu Evaluasi Khusus',
            'badge_color' => 'warning',
            'rekomendasi' => implode(' | ', $specificRecs),
            'rules' => $ruleExplanations,
            'keterangan' => 'Memerlukan pelatihan teknis spesifik untuk kriteria yang belum mencapai standar 70.'
        ];
    }

    // Default jika performa baik / memenuhi standar reguler
    return [
        'status' => 'Memuaskan',
        'badge_color' => 'success',
        'rekomendasi' => 'Workshop Lanjutan Modern Software Architecture & Inovasi Digital',
        'rules' => ['Standar: Semua kriteria >= 70'],
        'keterangan' => 'Pegawai memenuhi seluruh indikator capaian standar dengan baik.'
    ];
}

/**
 * Eksekusi Perhitungan SAW & Rule-Based Engine untuk periode tertentu
 */
function executeSpkCalculation(PDO $pdo, string $periode): array {
    // 1. Ambil data kriteria
    $stmtKriteria = $pdo->query("SELECT * FROM kriteria ORDER BY kode ASC");
    $kriteriaList = $stmtKriteria->fetchAll();
    
    $kriteriaMap = [];
    foreach ($kriteriaList as $k) {
        $kriteriaMap[strtolower($k['kode'])] = [
            'kode' => $k['kode'],
            'nama' => $k['nama_kriteria'],
            'bobot' => (float)$k['bobot'],
            'jenis' => strtolower($k['jenis'])
        ];
    }

    // 2. Ambil data penilaian pada periode tersebut
    $stmtPenilaian = $pdo->prepare("
        SELECT p.*, pg.nip, pg.nama, pg.departemen, pg.jabatan 
        FROM penilaian p
        JOIN pegawai pg ON p.pegawai_id = pg.id
        WHERE p.periode = :periode
        ORDER BY pg.nama ASC
    ");
    $stmtPenilaian->execute([':periode' => $periode]);
    $penilaianList = $stmtPenilaian->fetchAll();

    if (empty($penilaianList)) {
        return [
            'success' => false,
            'message' => "Tidak ada data penilaian untuk periode '$periode'.",
            'matrix_x' => [],
            'matrix_r' => [],
            'results' => []
        ];
    }

    // 3. Matriks Keputusan Awal (X) dan Cari Nilai Max/Min
    $matrixX = [];
    $extremes = [
        'c1' => ['max' => -INF, 'min' => INF],
        'c2' => ['max' => -INF, 'min' => INF],
        'c3' => ['max' => -INF, 'min' => INF],
        'c4' => ['max' => -INF, 'min' => INF],
    ];

    foreach ($penilaianList as $row) {
        $c1 = (float)$row['c1'];
        $c2 = (float)$row['c2'];
        $c3 = (float)$row['c3'];
        $c4 = (float)$row['c4'];

        $matrixX[] = [
            'penilaian_id' => $row['id'],
            'pegawai_id' => $row['pegawai_id'],
            'nip' => $row['nip'],
            'nama' => $row['nama'],
            'departemen' => $row['departemen'],
            'jabatan' => $row['jabatan'],
            'c1' => $c1,
            'c2' => $c2,
            'c3' => $c3,
            'c4' => $c4,
        ];

        // Hitung max dan min
        if ($c1 > $extremes['c1']['max']) $extremes['c1']['max'] = $c1;
        if ($c1 < $extremes['c1']['min']) $extremes['c1']['min'] = $c1;
        if ($c2 > $extremes['c2']['max']) $extremes['c2']['max'] = $c2;
        if ($c2 < $extremes['c2']['min']) $extremes['c2']['min'] = $c2;
        if ($c3 > $extremes['c3']['max']) $extremes['c3']['max'] = $c3;
        if ($c3 < $extremes['c3']['min']) $extremes['c3']['min'] = $c3;
        if ($c4 > $extremes['c4']['max']) $extremes['c4']['max'] = $c4;
        if ($c4 < $extremes['c4']['min']) $extremes['c4']['min'] = $c4;
    }

    // 4. Normalisasi Matriks (R)
    $matrixR = [];
    foreach ($matrixX as $row) {
        $r1 = 0; $r2 = 0; $r3 = 0; $r4 = 0;

        // C1 Normalisasi
        $j1 = $kriteriaMap['c1']['jenis'] ?? 'benefit';
        if ($j1 === 'benefit') {
            $r1 = ($extremes['c1']['max'] > 0) ? ($row['c1'] / $extremes['c1']['max']) : 0;
        } else {
            $r1 = ($row['c1'] > 0) ? ($extremes['c1']['min'] / $row['c1']) : 0;
        }

        // C2 Normalisasi
        $j2 = $kriteriaMap['c2']['jenis'] ?? 'benefit';
        if ($j2 === 'benefit') {
            $r2 = ($extremes['c2']['max'] > 0) ? ($row['c2'] / $extremes['c2']['max']) : 0;
        } else {
            $r2 = ($row['c2'] > 0) ? ($extremes['c2']['min'] / $row['c2']) : 0;
        }

        // C3 Normalisasi
        $j3 = $kriteriaMap['c3']['jenis'] ?? 'benefit';
        if ($j3 === 'benefit') {
            $r3 = ($extremes['c3']['max'] > 0) ? ($row['c3'] / $extremes['c3']['max']) : 0;
        } else {
            $r3 = ($row['c3'] > 0) ? ($extremes['c3']['min'] / $row['c3']) : 0;
        }

        // C4 Normalisasi
        $j4 = $kriteriaMap['c4']['jenis'] ?? 'benefit';
        if ($j4 === 'benefit') {
            $r4 = ($extremes['c4']['max'] > 0) ? ($row['c4'] / $extremes['c4']['max']) : 0;
        } else {
            $r4 = ($row['c4'] > 0) ? ($extremes['c4']['min'] / $row['c4']) : 0;
        }

        $matrixR[] = [
            'penilaian_id' => $row['penilaian_id'],
            'nip' => $row['nip'],
            'nama' => $row['nama'],
            'r1' => round($r1, 4),
            'r2' => round($r2, 4),
            'r3' => round($r3, 4),
            'r4' => round($r4, 4),
        ];
    }

    // 5. Perhitungan Nilai Preferensi (V) & Rule-Based Recommendation
    $w1 = $kriteriaMap['c1']['bobot'] ?? 0.20;
    $w2 = $kriteriaMap['c2']['bobot'] ?? 0.35;
    $w3 = $kriteriaMap['c3']['bobot'] ?? 0.25;
    $w4 = $kriteriaMap['c4']['bobot'] ?? 0.20;

    $results = [];
    foreach ($matrixX as $i => $raw) {
        $norm = $matrixR[$i];
        
        // V = w1*r1 + w2*r2 + w3*r3 + w4*r4
        $v = ($w1 * $norm['r1']) + ($w2 * $norm['r2']) + ($w3 * $norm['r3']) + ($w4 * $norm['r4']);
        $v = round($v, 4);

        // Evaluasi Rule-Based Engine
        $ruleEval = evaluateRuleBased($raw['c1'], $raw['c2'], $raw['c3'], $raw['c4'], $v);

        $results[] = [
            'penilaian_id' => $raw['penilaian_id'],
            'pegawai_id' => $raw['pegawai_id'],
            'nip' => $raw['nip'],
            'nama' => $raw['nama'],
            'departemen' => $raw['departemen'],
            'jabatan' => $raw['jabatan'],
            'c1' => $raw['c1'],
            'c2' => $raw['c2'],
            'c3' => $raw['c3'],
            'c4' => $raw['c4'],
            'r1' => $norm['r1'],
            'r2' => $norm['r2'],
            'r3' => $norm['r3'],
            'r4' => $norm['r4'],
            'nilai_akhir_v' => $v,
            'ranking' => 0, // diisi setelah sorting
            'rekomendasi_pelatihan' => $ruleEval['rekomendasi'],
            'rule_eval' => $ruleEval
        ];
    }

    // 6. Urutkan berdasarkan nilai V secara descending untuk mendapatkan Ranking
    usort($results, function ($a, $b) {
        return $b['nilai_akhir_v'] <=> $a['nilai_akhir_v'];
    });

    // 7. Berikan nomor ranking dan simpan ke tabel hasil_spk
    $pdo->beginTransaction();
    try {
        $stmtDelete = $pdo->prepare("
            DELETE h FROM hasil_spk h
            JOIN penilaian p ON h.penilaian_id = p.id
            WHERE p.periode = :periode
        ");
        $stmtDelete->execute([':periode' => $periode]);

        $stmtInsert = $pdo->prepare("
            INSERT INTO hasil_spk (penilaian_id, nilai_akhir_v, ranking, rekomendasi_pelatihan)
            VALUES (:penilaian_id, :nilai_akhir_v, :ranking, :rekomendasi_pelatihan)
        ");

        foreach ($results as $index => &$res) {
            $rank = $index + 1;
            $res['ranking'] = $rank;

            $stmtInsert->execute([
                ':penilaian_id' => $res['penilaian_id'],
                ':nilai_akhir_v' => $res['nilai_akhir_v'],
                ':ranking' => $rank,
                ':rekomendasi_pelatihan' => $res['rekomendasi_pelatihan']
            ]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'success' => false,
            'message' => 'Gagal menyimpan hasil perhitungan ke database: ' . $e->getMessage(),
            'matrix_x' => $matrixX,
            'matrix_r' => $matrixR,
            'results' => $results,
            'extremes' => $extremes,
            'kriteria' => $kriteriaMap
        ];
    }

    return [
        'success' => true,
        'message' => 'Kalkulasi SAW dan Rule-Based Engine berhasil dijalankan.',
        'matrix_x' => $matrixX,
        'matrix_r' => $matrixR,
        'results' => $results,
        'extremes' => $extremes,
        'kriteria' => $kriteriaMap
    ];
}
