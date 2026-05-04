# Skenario Testing Bab IV

Dokumen ini menyajikan skenario pengujian Whitebox dan Blackbox mengikuti format proposal Bab IV.

## A. Whitebox Testing

### Fokus Pengujian

- AhpCalculationService
- AhpMatrixService
- AhpCalculatorService
- AhpSettingsService

### Test Case dan Hasil Pengujian

| No  | Jalur Eksekusi                                                         | Input                                           | Output Aktual | Output yang Diharapkan                                      | Status      |
| --- | ---------------------------------------------------------------------- | ----------------------------------------------- | ------------- | ----------------------------------------------------------- | ----------- |
| 1   | calculateWeights() -> n==0 -> return []                                | matrix = []                                     | Belum diuji   | Mengembalikan array kosong                                  | Belum diuji |
| 2   | calculateWeights() -> validasi elemen <=0 -> throw exception           | matrix berisi nilai 0 pada salah satu sel       | Belum diuji   | Exception: Geometric mean invalid, semua nilai > 0          | Belum diuji |
| 3   | calculateWeights() -> geometric mean -> normalisasi bobot              | matrix 3x3 valid reciprocal                     | Belum diuji   | Bobot prioritas terbentuk, total bobot = 1                  | Belum diuji |
| 4   | calculateConsistencyRatio() -> n<=2 -> return 0                        | matrix 2x2 + weights valid                      | Belum diuji   | CR = 0                                                      | Belum diuji |
| 5   | calculateConsistencyRatio() -> validasi bobot <=0 -> throw exception   | matrix 3x3, weights mengandung 0                | Belum diuji   | Exception: bobot harus > 0                                  | Belum diuji |
| 6   | buildComparisonMatrix() -> comp.value<=0 -> skip pair                  | criteriaIds valid, salah satu pair value <=0    | Belum diuji   | Pair invalid di-skip, matrix tetap aman (tanpa pembagian 0) | Belum diuji |
| 7   | previewCalculation() -> n==0 -> throw exception                        | criteriaIds = []                                | Belum diuji   | Exception: Tidak ada kriteria untuk dikalkulasi             | Belum diuji |
| 8   | processLevel() -> n==1 -> weight=1.0                                   | 1 criteria sibling                              | Belum diuji   | weight=1, cr=0, status consistent                           | Belum diuji |
| 9   | calculate() -> columnSums[j]==0 branch                                 | matrix override sehingga 1 kolom bernilai 0     | Belum diuji   | Normalisasi kolom tersebut bernilai 0.0 tanpa crash         | Belum diuji |
| 10  | calculate() -> weightSum>0 -> renormalisasi                            | matrix valid 4x4                                | Belum diuji   | Semua bobot ternormalisasi dan jumlah = 1                   | Belum diuji |
| 11  | calculateFinalScore() -> stage=fakultas -> update total_score_fakultas | Registration stage fakultas, assessment lengkap | Belum diuji   | Nilai akhir tersimpan ke total_score_fakultas               | Belum diuji |
| 12  | calculateFinalScore() -> stage!=fakultas -> update total_score_univ    | Registration stage univ, assessment lengkap     | Belum diuji   | Nilai akhir tersimpan ke total_score_univ                   | Belum diuji |
| 13  | calculateCUScore() -> totalRaw>500 -> clamp 500                        | Total CU mentah 620                             | Belum diuji   | totalRaw dipotong 500, skor CU sesuai formula               | Belum diuji |
| 14  | calculateCUScore() -> cu root weight<=0 -> fallback 0.35               | root CU weight 0 / null                         | Belum diuji   | Bobot CU fallback 0.35                                      | Belum diuji |
| 15  | calculateJuriScore() -> max_score<=0 -> continue                       | criteria max_score = 0                          | Belum diuji   | Kriteria di-skip, tidak memicu pembagian 0                  | Belum diuji |
| 16  | updateWeight() -> bobot<0 atau >100 -> throw exception                 | weightPercentage = -10 atau 120                 | Belum diuji   | Exception: Bobot harus antara 0% sampai 100%                | Belum diuji |
| 17  | updateWeight() -> sub-kriteria CU -> throw exception                   | id milik child criteria type cu                 | Belum diuji   | Exception: Bobot tidak boleh diubah pada sub-kriteria CU    | Belum diuji |
| 18  | updateWeight() -> repository update gagal -> throw exception           | id valid, repository mengembalikan false        | Belum diuji   | Exception: Gagal mengupdate kriteria                        | Belum diuji |

## B. Blackbox Testing

### Test Case dan Hasil Pengujian

| No  | Kasus Uji                                    | Skenario                                                  | Hasil yang Diharapkan                                                | Hasil Pengujian | Kesimpulan  |
| --- | -------------------------------------------- | --------------------------------------------------------- | -------------------------------------------------------------------- | --------------- | ----------- |
| 1   | Login berhasil                               | User memasukkan email dan password valid                  | Redirect ke dashboard sesuai role                                    | Belum diuji     | Belum diuji |
| 2   | Login gagal                                  | User memasukkan email/password salah                      | Pesan error tampil, tetap di halaman login                           | Belum diuji     | Belum diuji |
| 3   | Akses route tanpa login                      | Buka route protected secara langsung                      | Redirect ke halaman login                                            | Belum diuji     | Belum diuji |
| 4   | Akses route role tidak sesuai                | Mahasiswa membuka route admin/juri                        | Ditolak dengan 403 atau redirect sesuai kebijakan sistem             | Belum diuji     | Belum diuji |
| 5   | Registrasi akun baru                         | Isi form registrasi valid                                 | Akun berhasil dibuat dan dapat login                                 | Belum diuji     | Belum diuji |
| 6   | Update profil mahasiswa                      | Mahasiswa mengubah data profil akademik valid             | Data profil tersimpan dan tampil pada halaman profil                 | Belum diuji     | Belum diuji |
| 7   | Update profil dosen                          | Dosen mengubah data profil valid                          | Data profil dosen tersimpan                                          | Belum diuji     | Belum diuji |
| 8   | Registrasi peserta pilmapres valid           | Mahasiswa mengisi form registrasi dan upload berkas valid | Data registrasi tersimpan, status peserta aktif                      | Belum diuji     | Belum diuji |
| 9   | Registrasi peserta dengan berkas tidak valid | Upload tipe/ukuran file tidak sesuai aturan               | Sistem menolak upload dan menampilkan pesan validasi                 | Belum diuji     | Belum diuji |
| 10  | Tambah capaian unggulan valid                | Mahasiswa menambah data achievement lengkap               | Data achievement tersimpan dan muncul di daftar                      | Belum diuji     | Belum diuji |
| 11  | Hapus capaian unggulan                       | Mahasiswa menghapus achievement                           | Data achievement terhapus dari daftar                                | Belum diuji     | Belum diuji |
| 12  | Input penilaian juri valid                   | Dosen mengisi nilai semua kriteria sesuai rentang         | Nilai tersimpan dan dapat ditinjau kembali                           | Belum diuji     | Belum diuji |
| 13  | Input penilaian juri tidak valid             | Dosen mengisi nilai di luar rentang                       | Sistem menolak dan menampilkan pesan validasi                        | Belum diuji     | Belum diuji |
| 14  | Kelola kriteria admin                        | Admin menambah/mengubah/menghapus kriteria valid          | Perubahan kriteria tersimpan dan tampil di daftar                    | Belum diuji     | Belum diuji |
| 15  | Kelola pairwise comparison valid             | Admin mengisi perbandingan berpasangan antar kriteria     | Nilai tersimpan, konsistensi dihitung                                | Belum diuji     | Belum diuji |
| 16  | Kelola pairwise comparison tidak valid       | Admin mengisi nilai kosong/di luar skala                  | Sistem menolak input dan menampilkan error                           | Belum diuji     | Belum diuji |
| 17  | Kelola periode seleksi                       | Admin membuat periode aktif baru                          | Periode tersimpan, hanya periode aktif yang digunakan proses ranking | Belum diuji     | Belum diuji |
| 18  | Ranking peserta                              | Admin membuka halaman ranking setelah data nilai lengkap  | Ranking tampil urut berdasarkan skor akhir                           | Belum diuji     | Belum diuji |
| 19  | Export ranking PDF                           | Admin menekan export PDF ranking                          | File PDF ranking terunduh dan dapat dibuka                           | Belum diuji     | Belum diuji |
| 20  | Delegasi peserta ke tahap berikutnya         | Admin melakukan delegate pemenang                         | Status peserta berubah menjadi terdelegasi                           | Belum diuji     | Belum diuji |
| 21  | Pembatalan delegasi                          | Admin cancel delegate peserta                             | Status delegasi peserta kembali seperti semula                       | Belum diuji     | Belum diuji |
| 22  | Halaman transparansi publik internal         | User login membuka menu transparansi                      | Data ringkasan dan detail hasil dapat diakses                        | Belum diuji     | Belum diuji |
| 23  | Export transparansi PDF                      | User login mengekspor transparansi PDF                    | File PDF transparansi berhasil diunduh                               | Belum diuji     | Belum diuji |
| 24  | Kelola juri (super admin/admin)              | Admin/super admin menambah atau edit data juri            | Data juri tersimpan dan bisa digunakan untuk penilaian               | Belum diuji     | Belum diuji |
| 25  | Kelola panitia (super admin)                 | Super admin menambah/hapus panitia                        | Data panitia berubah sesuai aksi                                     | Belum diuji     | Belum diuji |

## Catatan Pengisian

- Kolom Hasil Pengujian, Output Aktual, dan Status/Kesimpulan diisi saat eksekusi testing.
- Gunakan nilai Status: Pass/Fail untuk Whitebox dan Kesimpulan: Berhasil/Gagal untuk Blackbox agar konsisten dengan proposal.
