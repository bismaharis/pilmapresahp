# Analisis Pembaruan: White Box Testing Pilmapres

## Perbandingan Struktur Lama vs Baru

### 1. Perubahan Fokus dan Kedalaman

#### ❌ Pendekatan Lama

- Fokus hanya pada modul perhitungan AHP (calculateFinalScore)
- Menjelaskan hanya 3 tahap utama: Data, Perhitungan Skor, Perhitungan Global Weight
- Test case terbatas pada 4 jalur basis dari cyclomatic complexity
- Tidak menjelaskan modul-modul pendukung lainnya

#### ✅ Pendekatan Baru

- **Menyeluruh**: Mencakup 5 modul utama:
    1. Autentikasi Pengguna (5 test cases)
    2. Pemrosesan Data Peserta (13 test cases)
    3. Penilaian Juri (10 test cases)
    4. Perhitungan AHP (13 test cases)
    5. Transparansi dan Pelaporan (5 test cases)
- **Total 46 test cases** dengan coverage 100%
- Penjelasan sistematis dari input hingga output setiap modul

---

### 2. Detail Penjelasan Logika Program

#### ❌ Lama: Tahap-Tahap Abstrak

```
a. Pengambilan Data
b. Perhitungan Skor Capaian Unggulan
c. Perhitungan Skor Gagasan Kreatif dan Bahasa Inggris
d. Perhitungan Global Weight
```

#### ✅ Baru: Alur Eksekusi Konkret

Setiap tahap dijelaskan dengan detail:

**Contoh: Modul Autentikasi**

- Input: email + password
- Verifikasi email di basis data
- Jika tidak ditemukan → error "Email tidak terdaftar"
- Jika ditemukan → hash comparison password
- Jika password salah → error "Password salah"
- Jika benar → baca role → buat session → redirect sesuai role

**Contoh: Modul Pemrosesan Data Peserta - Validasi Periode Pendaftaran**

```
if (current_date < start_date) {
    display "Belum Tersedia"
} elseif (current_date > end_date) {
    display "Telah Berakhir"
} else {
    display form_pendaftaran
    // Validasi file format, ukuran, dst
    // Simpan ke tabel registrations
}
```

---

### 3. Error Handling

#### ❌ Lama: Tidak Dijelaskan

- Tidak ada penjelasan tentang bagaimana sistem menangani error
- Tidak ada test case untuk input yang salah

#### ✅ Baru: Error Handling Terstruktur

Setiap modul mempunyai error handling:

| Modul               | Error yang Dihandle                                                                                                                |
| ------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| **Autentikasi**     | Email tidak terdaftar, Password salah                                                                                              |
| **Pemrosesan Data** | Format email invalid, Ukuran file melebihi limit, Format file tidak diizinkan, Periode belum dibuka, Limit sertifikat terlampaui   |
| **Penilaian Juri**  | Akses peserta bukan dari fakultas yang sama, Skor di luar range 0-100, Format skor bukan numeric, Periode penilaian telah berakhir |
| **Perhitungan AHP** | Tidak ada assessment (return 0), Limit capaian unggulan terlampaui                                                                 |

---

### 4. Penjelasan Perhitungan AHP

#### ❌ Lama: Singkat dan Kurang Detail

```
Perhitungan Skor Capaian Unggulan:
"Untuk setiap kriteria bertipe cu, sistem mengambil semua assessment yang
memiliki criteria_id cocok dengan sub-kriteria CU, kemudian merata-ratakan
nilai antar juri per sub-kriteria menggunakan groupBy('criteria_id')->sum(avg('score'))."
```

#### ✅ Baru: Langkah per Langkah dengan Pseudocode

```
Tahap 2: Perhitungan Skor Capaian Unggulan (CU)

1. Identifikasi kriteria dengan tipe "cu"
2. Untuk setiap sub-kriteria cu:
   - Ambil semua achievements yang cocok dengan sub_criteria_id
   - Kumpulkan semua assessment yang referensi ke sub_criteria_id
   - Kelompokkan berdasarkan lecturer_id
   - Untuk setiap juri:
     avg_score_per_juri = SUM(scores) / COUNT(scores)
   - Hitung rata-rata lintas juri:
     avg_cu_score = SUM(avg_score_per_juri) / COUNT(juri)
3. Terapkan batasan: max 4 file per sub-kriteria, 10 file total
4. Jika limit terlampaui: gunakan nilai 0 untuk sub-kriteria berlebihan
```

**Test Case Konkret:**

- Input: CU_sub1 = [cert1, cert2, cert3, cert4, cert5]
- Sistem hanya menggunakan 4 file pertama, mengabaikan cert5
- Output: CU_score = (juri1_avg + juri2_avg + ... ) / count_juri (hanya 4 file)

---

### 5. Penjelasan Tahapan Alur Keseluruhan

#### ❌ Lama: Flowchart Tanpa Narasi

- Hanya menampilkan Gambar 4.41 dan Gambar 4.42
- Tidak ada penjelasan detail tentang setiap fase

#### ✅ Baru: Narasi Lengkap 7 Fase

```
Fase 1: Registrasi dan Pendaftaran Peserta
├─ Peserta login (autentikasi)
├─ Akses halaman pendaftaran
├─ Sistem cek status periode
├─ Validasi file
└─ Simpan ke tabel registrations

Fase 2: Pengelolaan Capaian Unggulan
├─ Peserta tambah capaian
├─ Validasi limit (4 per sub-kriteria, 10 total)
└─ Simpan ke tabel achievements

Fase 3: Penilaian oleh Juri
├─ Juri login
├─ Akses daftar peserta (hanya dari fakultas yang sama)
├─ Buka form penilaian
├─ Lihat berkas peserta
├─ Isi skor dan komentar per kriteria
└─ Simpan assessment

Fase 4: Perhitungan Nilai Akhir (Modul AHP)
├─ Ambil assessments dari semua juri
├─ Kelompokkan per criteria_id
├─ Hitung rata-rata lintas juri
├─ Perhitungan CU dengan batasan limit
├─ Normalisasi GK dan BI ke skala 0-100
├─ Hitung bobot global
└─ Final score = SUM(normalized_score * global_weight)

Fase 5: Pemeringkatan
├─ Ambil semua final_score
├─ Urutkan descending
└─ Assign rank (dengan tie handling)

Fase 6: Pengambilan Keputusan dan Delegasi
├─ Admin/Super Admin akses delegasi
├─ Approve peserta ke tahap berikutnya
└─ Update status peserta

Fase 7: Transparansi Hasil
├─ Tampilkan penilaian setiap juri
├─ Tampilkan bobot, skor, skor max, nilai normalisasi
├─ Hitung rata-rata lintas juri
└─ Sediakan unduh PDF
```

---

### 6. Test Case Coverage

#### ❌ Lama

- **Total test case**: 8 (4 basis path + 4 skenario)
- **Coverage**: Hanya modul perhitungan AHP
- **Error handling**: Tidak ada test untuk error case

#### ✅ Baru

- **Total test case**: 46
- **Coverage**:
    - Autentikasi: 5 test
    - Pemrosesan Data: 13 test (termasuk error handling)
    - Penilaian Juri: 10 test (termasuk conflict of interest, edit control)
    - Perhitungan AHP: 13 test (termasuk edge case)
    - Transparansi: 5 test
- **Error handling**: ~30 test cases untuk validasi dan error handling

---

### 7. Tabel Perbandingan Aspek Pengujian

| Aspek                     | Lama               | Baru                                                  |
| ------------------------- | ------------------ | ----------------------------------------------------- |
| **Modul yang Diuji**      | 1 (AHP)            | 5 (Auth, Data, Penilaian, AHP, Transparansi)          |
| **Total Test Cases**      | 8                  | 46                                                    |
| **Error Handling**        | ❌ Tidak ada       | ✅ ~30 cases                                          |
| **Conflict of Interest**  | ❌ Tidak dibahas   | ✅ 2 test cases                                       |
| **Limit Control**         | ✅ Ada (dasar)     | ✅ Terperinci (5 test cases)                          |
| **Normalisasi Score**     | ✅ Ada             | ✅ Terperinci dengan contoh                           |
| **Bobot Global**          | ✅ Ada             | ✅ Terperinci dengan formula                          |
| **Pemeringkatan**         | ❌ Tidak ada       | ✅ 2 test cases (tie handling)                        |
| **Transparansi**          | ❌ Tidak ada       | ✅ 5 test cases                                       |
| **Cyclomatic Complexity** | ✅ Ada             | ✅ Ada + penjelasan 4 jalur basis konkret             |
| **Narasi Alur Lengkap**   | ❌ Hanya flowchart | ✅ 7 fase dengan detail                               |
| **Format Test Case**      | Tabel sederhana    | Tabel lengkap: Input, Output Aktual, Expected, Status |

---

### 8. Penjelasan Fitur yang Ditambahkan

#### ✅ **Conflict of Interest Handling (BARU)**

```
Test Case:
Juri FMIPA mengakses daftar peserta
→ Sistem filter: tampilkan hanya peserta dari FMIPA

Juri FMIPA mencoba akses peserta dari FKIP
→ Sistem: "Akses ditolak - peserta bukan dari fakultas Anda"
```

#### ✅ **Edit Control Penilaian (BARU)**

```
Test Case:
Juri memberikan skor 75, periode masih aktif
→ Sistem: score dapat diedit, timestamp diupdate

Juri mencoba edit skor, periode sudah berakhir
→ Sistem: "Periode penilaian telah berakhir, tidak bisa edit"
```

#### ✅ **Batasan Capaian Unggulan (DITINGKATKAN)**

```
Lama: Hanya mention "maksimal 10"
Baru:
- Test case 1: Tambah 4 pada sub-kategori A → OK
- Test case 2: Tambah ke-5 pada sub-kategori A → Error
- Test case 3: Tambah file ke-10 keseluruhan → OK
- Test case 4: Tambah file ke-11 keseluruhan → Error
```

#### ✅ **Transparansi Penilaian (BARU)**

```
- Tampilkan skor dari setiap juri
- Tampilkan bobot penilaian
- Tampilkan skor maksimum
- Tampilkan nilai normalisasi
- Tampilkan rata-rata lintas juri
- Sediakan unduh PDF
```

---

### 9. Struktur Penulisan Skripsi

#### ❌ Lama

1. Analisis Struktur Kode dan Logika Perhitungan AHP
    - 4 sub-bagian (a, b, c, d) yang sangat teknis
2. Alur Logika
    - Penjelasan flowchart tanpa detail
3. Testcase dan Hasil Pengujian
    - Hanya untuk cyclomatic complexity
4. Test Case dan Hasil Pengujian Kode
    - 5 skenario tabel
5. Evaluasi
    - Ringkasan singkat

#### ✅ Baru

1. **Pendahuluan**
    - Konteks dan tujuan white box testing yang jelas

2. **Analisis Struktur Kode dan Logika Perhitungan** (5 sub-bab)
    - 1.1 Modul Autentikasi
    - 1.2 Modul Pemrosesan Data Peserta
    - 1.3 Modul Penilaian Juri
    - 1.4 Modul Perhitungan AHP
    - 1.5 Modul Transparansi dan Pelaporan

    Setiap modul mencakup:
    - Deskripsi
    - Alur Logika (step-by-step)
    - Komponen Kritis

3. **Alur Logika Sistem Secara Keseluruhan**
    - 7 fase lengkap dari registrasi hingga transparansi

4. **Test Case dan Hasil Pengujian** (5 sub-bagian)
    - 3.1 Autentikasi (5 test cases)
    - 3.2 Pemrosesan Data (13 test cases)
    - 3.3 Penilaian Juri (10 test cases)
    - 3.4 Perhitungan AHP (13 test cases)
    - 3.5 Transparansi (5 test cases)

5. **Analisis Cyclomatic Complexity**
    - 4.1 Komponen Flowgraph
    - 4.2 Perhitungan (3 metode)
    - 4.3 Jalur Basis Eksekusi dengan test cases konkret

6. **Evaluasi dan Kesimpulan**
    - 5 poin utama kesuksesan pengujian
    - Tabel ringkasan hasil akhir (46 test cases, 100% pass)

---

### 10. Keunggulan Pendekatan Baru

| Keunggulan             | Penjelasan                                                    |
| ---------------------- | ------------------------------------------------------------- |
| **Komprehensif**       | Mencakup 5 modul berbeda, bukan hanya AHP                     |
| **Terstruktur**        | Setiap modul memiliki deskripsi, alur logika, komponen kritis |
| **Sistematis**         | Dari error handling hingga edge case, semua ditest            |
| **Mudah Diverifikasi** | Test case konkret dengan input/output spesifik                |
| **Sesuai Skripsi**     | Alur penulisan akademis yang rapi dan logis                   |
| **Coverage Lengkap**   | 100% coverage dengan 46 test cases yang terdokumentasi        |
| **Practical**          | Menjelaskan hal nyata yang terjadi di sistem, bukan teoritis  |
| **Accountability**     | Setiap modul jelas tanggung jawabnya dan hasil test case-nya  |

---

## Kesimpulan Analisis Pembaruan

Pendekatan **baru** merupakan peningkatan signifikan dari pendekatan lama dalam beberapa aspek:

1. **Cakupan**: Dari 1 modul → 5 modul (5x lebih luas)
2. **Test Case**: Dari 8 → 46 test cases (6x lebih banyak)
3. **Detail Alur**: Dari flowchart abstrak → narasi 7 fase konkret
4. **Error Handling**: Dari tidak ada → 30+ test cases untuk validasi
5. **Verifikasi**: Dari sulit dipahami → mudah diverifikasi dengan test cases spesifik

Dengan pembaruan ini, bab White Box Testing menjadi lebih komprehensif, akademis, terstruktur, dan siap untuk penulisan skripsi formal yang berkualitas tinggi.
