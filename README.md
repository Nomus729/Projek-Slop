# Sistem Penunjang Keputusan (SPK) Penilaian Kinerja Talenta IT & Rekomendasi Pelatihan
## PT Indobuzz Republik Digital

Aplikasi web Sistem Penunjang Keputusan (SPK) untuk evaluasi kinerja talenta IT dan rekomendasi program pelatihan teknologi di lingkungan **PT Indobuzz Republik Digital** (Digital Marketing Agency & IT Software Solutions — [indobuzz.co.id](https://indobuzz.co.id/)).

Sistem mengombinasikan algoritma **Simple Additive Weighting (SAW)** untuk perankingan kinerja objektif dan **Rule-Based Inference Engine** untuk pemetaan rekomendasi pelatihan keahlian IT secara otomatis.

---

## 🚀 Panduan Menjalankan Sistem

1. **Pastikan Web Server XAMPP Aktif:**
   - Jalankan modul **Apache** dan **MySQL** melalui XAMPP Control Panel.
2. **Import Database:**
   - Berkas dump database MySQL lengkap tersedia di: **`spk_kinerja_pegawai.sql`** (folder utama) dan **`database/spk_kinerja_pegawai.sql`**.
   - Buka **phpMyAdmin** (`http://localhost/phpmyadmin/`) &rarr; klik tab **Import** &rarr; pilih berkas `spk_kinerja_pegawai.sql` &rarr; klik **Import / Kirim**. *(Database `spk_kinerja_pegawai` beserta seluruh tabel dan datanya akan dibuat otomatis).*
   - Konfigurasi koneksi database tersimpan di [`config/database.php`](file:///c:/xampp/htdocs/ProyekSPK/config/database.php) (Default: Host `127.0.0.1`, User `root`, Password kosong).
3. **Akses Aplikasi via Browser:**
   - Buka peramban web dan kunjungi: **`http://localhost/ProyekSPK/`**

### 🔑 Akun Login Bawaan (Default Credentials)
| Role | Username | Password | Keterangan |
|---|---|---|---|
| **Tech Lead / Admin** | `admin` | `admin123` | Hak akses penuh sistem |
| **People Operations / HR IT** | `hrd_penilai` | `admin123` | Akses penilaian talenta IT |

*(Tersedia tombol pintas pengisian akun demo langsung pada halaman login).*

---

## 🎨 Design System & Estetika Visual

- **Primary Color:** `#1E3A8A` (Deep Tech Navy) — Sidebar, Header, tombol utama.
- **Secondary / Accent:** `#0D9488` (Teal) — Highlight peringkat teratas, badge aktif, tombol sekunder.
- **Background Utama:** `#F8FAFC` (Slate Neutral Light) — Tampilan kanvas yang bersih dan rapi.
- **Card & Surface:** `#FFFFFF` (Pure White) — Wadah tabel data dan metrik kartu statistik.
- **Tipografi:** Google Font *Inter*, sans-serif.

---

## 📊 Alur Kerja & Modul Sistem

1. **Dashboard Eksekutif (`dashboard.php`):** Ringkasan metrik statistik talenta IT, 5 peringkat teratas, distribusi rekomendasi pelatihan, dan bobot kriteria.
2. **Master Pegawai IT (`pegawai.php`):** Pengelolaan data talenta IT dengan filter divisi, pencarian NIP/Nama, modal tambah/edit, serta hapus data.
3. **Master Kriteria (`kriteria.php`):** Konfigurasi kriteria evaluasi IT C1–C4, tipe (*Benefit*), validasi total bobot 100%, dan tombol reset default.
4. **Input Penilaian (`penilaian.php`):** Formulir input berkala capaian aktual skala 1–100 per periode evaluasi, dengan penanda peringatan nilai di bawah standar (< 70).
5. **Komputasi SPK & Rules (`komputasi.php`):** Transparansi komputasi 3 tahap:
   - **Tahap 1:** Matriks Nilai Awal dan nilai ekstrem kolom (Maksimum & Minimum).
   - **Tahap 2:** Matriks Normalisasi sesuai sifat kriteria *Benefit*.
   - **Tahap 3:** Skor Akhir, Ranking, serta evaluasi inferensi *Rule-Based Engine*.
6. **Laporan Rekapitulasi (`laporan.php`, `cetak.php`, & `export_excel.php`):** Format cetak resmi berstandar korporat dengan Kop Surat PT Indobuzz Republik Digital, filter divisi, ekspor Excel spreadsheet (`.xls`), dan pengesahan CEO.

---

## 📐 Kriteria Evaluasi Talenta IT & Aturan Rule-Based

### Kriteria dan Bobot
- **C1:** Presensi & Standup Agile (Benefit, Bobot 20% / 0.20)
- **C2:** KPI & Delivery Proyek (Benefit, Bobot 35% / 0.35)
- **C3:** Kualitas Kode & SOP IT (Benefit, Bobot 25% / 0.25)
- **C4:** Kolaborasi & Komunikasi Tim (Benefit, Bobot 20% / 0.20)

### Aturan Inferensi Pelatihan IT (Rule-Based Engine)
- **Rule 1:** `IF (C2 < 70)` $\rightarrow$ Pelatihan Software Engineering & Delivery Proyek
- **Rule 2:** `IF (C3 < 70)` $\rightarrow$ Workshop Code Quality, IT Security & DevOps CI/CD
- **Rule 3:** `IF (C1 < 70)` $\rightarrow$ Workshop Manajemen Waktu & Kedisiplinan Kerja Agile
- **Rule 4:** `IF (C4 < 70)` $\rightarrow$ Pelatihan Komunikasi Efektif & Kolaborasi Tim Developer
- **Rule 5:** `IF (Skor Akhir < 0.60 OR > 2 kriteria < 70)` $\rightarrow$ Program Pendampingan Khusus (Performance Improvement Plan / PIP)
- **Rule 6:** `IF (Skor Akhir >= 0.85 AND seluruh kriteria >= 75)` $\rightarrow$ Dipertimbangkan untuk Promosi Jabatan / Jalur Tech Lead & Principal Specialist

---

## 🏢 Profil Instansi
- **Perusahaan:** PT Indobuzz Republik Digital
- **Website:** https://indobuzz.co.id/
- **Pimpinan:** Jeni Lutfi, S.T. (Chief Executive Officer)
- **Alamat Operasional:** Jl. Otto Iskandardinata No. 16, Subang, Jawa Barat 41211
