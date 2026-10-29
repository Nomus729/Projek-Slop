# SPESIFIKASI SISTEM DAN ATURAN PENGEMBANGAN SISTEM INFORMASI SPK

**Judul Proyek:** Sistem Penunjang Keputusan Penilaian Kinerja Talenta IT dan Penentuan Rekomendasi Pelatihan Berbasis Web  
**Studi Kasus:** PT Indobuzz Republik Digital (Digital Marketing Agency & IT Software Solutions — https://indobuzz.co.id/)  
**Metode Komputasi:** Kombinasi *Simple Additive Weighting* (SAW) & *Rule-Based Inference Engine*  

---

## 1. Profil Perusahaan & Studi Kasus

- **Nama Instansi:** PT Indobuzz Republik Digital
- **Bidang Usaha:** Digital Marketing Agency, Software Engineering, IT Development, SEO & Social Media Solutions
- **Website:** [indobuzz.co.id](https://indobuzz.co.id/)
- **Kantor Operasional:** Jl. Otto Iskandardinata No. 16, Subang, Jawa Barat 41211 / Jakarta
- **Target Pengguna:** People Operations / Divisi HR IT & Manajemen Engineering

---

## 2. Design System & Panduan Visual (UI/UX)

Untuk menciptakan antarmuka yang bersih, modern, dan profesional bagi talenta teknologi informasi:

### Palet Warna (Color Palette)
- **Primary Color:** `#1E3A8A` (Deep Tech Navy) — Sidebar, Header, tombol aksi utama, dan aksen tabel.
- **Secondary / Accent Color:** `#0D9488` (Teal Modern) — Lencana status, highlight perankingan terbaik, dan tombol sekunder.
- **Background Utama:** `#F8FAFC` (Slate Neutral Light) — Latar belakang kanvas yang bersih dan tidak melelahkan mata.
- **Card & Surface Background:** `#FFFFFF` (Pure White) — Wadah tabel data, formulir, dan metrik statistik.
- **Text Primary:** `#0F172A` (Slate 900) — Tipografi judul dan teks bacaan utama.
- **Text Muted:** `#64748B` (Slate 500) — Subteks, keterangan formulir, dan label sekunder.
- **Status Alert Colors:**
  - **Success / Memuaskan:** `#16A34A` (Emerald Green)
  - **Warning / Perlu Pengembangan:** `#D97706` (Amber Yellow)
  - **Danger / Pembinaan Khusus:** `#DC2626` (Rose Red)

### Tipografi & Komponen
- **Font Family:** `Inter`, `Segoe UI`, `sans-serif` (bersih, keterbacaan tinggi tanpa simbol-simbol rumit yang menumpuk).
- **Layout:** *Sidebar Navigation* di sisi kiri (lebar 256px) + *Main Content Area* responsif dengan batas container maksimum 1280px.
- **Komponen Tabel:** Border halus (`border-slate-200`), *striped rows* berselang-seling tipis (`hover:bg-slate-50`), serta *badge* status berkode warna.

---

## 3. Arsitektur Alur Sistem (System Flow)

1. **Autentikasi:** HR IT / Penilai masuk ke sistem menggunakan kredensial terdaftar.
2. **Master Data Talenta IT & Kriteria:**
   - Kelola data profil talenta IT (NIP, Nama, Divisi Tech, Jabatan IT).
   - Pengaturan bobot kriteria evaluasi kinerja IT (Total bobot = 100%).
3. **Input Capaian Kinerja Aktual:**
   - Input nilai riil per kriteria evaluasi (skala 1–100) per periode penilaian (misal: `2026-Semester-2`).
4. **Eksekusi Engine SPK:**
   - **Tahap Normalisasi SAW:** Matriks ternormalisasi dihitung berdasarkan sifat kriteria *Benefit*.
   - **Tahap Penjumlahan Terbobot:** Skor preferensi akhir dihitung dari akumulasi perkalian bobot kriteria dengan nilai normalisasi.
   - **Tahap Rule-Based Engine:** Sistem mendiagnosis nilai per kriteria dan skor akhir untuk menentukan rekomendasi pelatihan dan pengembangan yang relevan secara otomatis.
5. **Output & Pelaporan:**
   - Dashboard eksekutif dengan statistik performa tim IT.
   - Transparansi tahapan komputasi SPK.
   - Cetak lembar laporan rekapitulasi resmi dan ekspor format spreadsheet Excel.

---

## 4. Kriteria Penilaian Talenta IT & Bobot Preferensi

| Kode | Nama Kriteria | Tipe Kriteria | Bobot | Indikator Kompetensi IT |
|---|---|---|---|---|
| **C1** | Presensi & Standup Agile | Benefit | 20% (0.20) | Kehadiran harian, ketepatan waktu sprint daily standup & komitmen jam kerja tim IT |
| **C2** | KPI & Delivery Proyek | Benefit | 35% (0.35) | Penyelesaian sprint backlog, ketepatan jadwal rilis produk/fitur, dan kepuasan klien/PO |
| **C3** | Kualitas Kode & SOP IT | Benefit | 25% (0.25) | Standar clean code, code review pass rate, zero bug critical & kepatuhan git workflow |
| **C4** | Kolaborasi & Komunikasi Tim | Benefit | 20% (0.20) | Komunikasi efektif lintas divisi tech (frontend, backend, QA, UI/UX) dan proaktif knowledge sharing |

---

## 5. Logika Inferensi Rekomendasi Pelatihan (Rule-Based Engine)

Sistem secara cerdas memetakan rekomendasi program pengembangan berdasarkan capaian talenta IT:

- **Rule 1 (Peningkatan Delivery & Rekayasa Software):**  
  `IF (C2 < 70) THEN Rekomendasi = "Pelatihan Software Engineering & Delivery Proyek"`
- **Rule 2 (Kualitas Kode, Keamanan & CI/CD):**  
  `IF (C3 < 70) THEN Rekomendasi = "Workshop Code Quality, IT Security & DevOps CI/CD"`
- **Rule 3 (Manajemen Waktu & Kedisiplinan Sprint):**  
  `IF (C1 < 70) THEN Rekomendasi = "Workshop Manajemen Waktu & Kedisiplinan Kerja Agile"`
- **Rule 4 (Komunikasi Tim Developer):**  
  `IF (C4 < 70) THEN Rekomendasi = "Pelatihan Komunikasi Efektif & Kolaborasi Tim Developer"`
- **Rule 5 (Pendampingan Khusus):**  
  `IF (Skor Akhir < 0.60 ATAU lebih dari dua kriteria bernilai < 70) THEN Rekomendasi = "Program Pendampingan Khusus (Performance Improvement Plan / PIP)"`
- **Rule 6 (Prestasi Unggul & Jalur Promosi):**  
  `IF (Skor Akhir >= 0.85 DAN seluruh kriteria >= 75) THEN Rekomendasi = "Dipertimbangkan untuk Promosi Jabatan / Jalur Tech Lead & Principal Specialist"`

---

## 6. Struktur Basis Data (MySQL)

1. **`users`**: Akun penilai & HR IT (`id`, `username`, `password`, `nama_lengkap`, `role`).
2. **`pegawai`**: Profil talenta IT (`id`, `nip`, `nama`, `departemen`, `jabatan`).
3. **`kriteria`**: Parameter evaluasi (`id`, `kode`, `nama_kriteria`, `bobot`, `jenis`, `keterangan`).
4. **`penilaian`**: Capaian aktual per periode (`id`, `pegawai_id`, `periode`, `c1`, `c2`, `c3`, `c4`).
5. **`hasil_spk`**: Skor akhir komputasi (`id`, `penilaian_id`, `nilai_akhir_v`, `ranking`, `rekomendasi_pelatihan`).
