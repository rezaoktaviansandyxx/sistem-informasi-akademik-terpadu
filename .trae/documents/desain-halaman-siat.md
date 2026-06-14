## 1. Arah Desain
SIAT menggunakan pendekatan visual institusional-modern: formal, bersih, data-driven, dan dipercaya. Fokus utama desain adalah keterbacaan data akademik, kecepatan tindakan, dan kejelasan status proses.

- Pendekatan: desktop-first dengan optimalisasi tablet.
- Karakter visual: profesional, ringan, kontras jelas, tidak dekoratif berlebihan.
- Prinsip: semua layar harus mengutamakan keterbacaan tabel, status, filter, dan workflow approval.

## 2. Sistem Visual
### 2.1 Warna
| Token | Warna | Fungsi |
|------|-------|--------|
| Primary 900 | `#102A43` | sidebar, header, area fokus institusi |
| Primary 600 | `#1D4ED8` | tombol utama, link, highlight aktif |
| Primary 100 | `#DBEAFE` | background status aktif dan chip |
| Neutral 0 | `#FFFFFF` | surface utama |
| Neutral 50 | `#F8FAFC` | background halaman |
| Neutral 200 | `#E2E8F0` | border, divider |
| Success | `#15803D` | status berhasil |
| Warning | `#CA8A04` | SLA mendekati tenggat, kapasitas hampir penuh |
| Danger | `#DC2626` | penolakan, konflik, risiko |

### 2.2 Tipografi
| Elemen | Ukuran | Berat |
|--------|--------|-------|
| Heading halaman | 28-32 | 700 |
| Heading section | 20-24 | 600 |
| Heading kartu | 16-18 | 600 |
| Teks isi | 14-16 | 400 |
| Caption dan meta | 12-13 | 400 |

### 2.3 Komponen Inti
| Komponen | Karakter |
|----------|----------|
| Sidebar | fixed di desktop, collapsible di tablet |
| App bar | menampilkan peran aktif, pencarian, notifikasi, profil |
| Stat card | angka utama, label, tren, badge |
| Data table | sticky header, filter, sorting, pagination |
| Drawer detail | untuk view data detail tanpa meninggalkan daftar |
| Wizard import | stepper validasi file dan preview |
| Approval card | status, SLA, pelaku, tindakan cepat |
| Timeline log | urutan aktivitas dan perubahan |

## 3. Peta Halaman Flutter Web
| Halaman | Tujuan | Modul |
|---------|--------|-------|
| Login | akses awal pengguna | auth |
| Forgot Password | permintaan tautan reset | auth |
| Reset Password | ubah password via token | auth |
| Dashboard | ringkasan per peran | dashboard |
| KRS Online | pengisian KRS mahasiswa | academic |
| KHS | tampilan hasil studi per semester | academic |
| Transkrip | riwayat nilai kumulatif | academic |
| Jadwal Kuliah | jadwal mahasiswa | academic |
| Presensi Mahasiswa | presensi per pertemuan | academic |
| Kelas Dosen | daftar kelas dosen | lecturer |
| Input Nilai | draft dan finalisasi nilai | lecturer |
| Rekap Mengajar | presensi dan progres dosen | lecturer |
| Profil | data pengguna, change password, active session | profile |
| Reports | daftar laporan dan hasil ekspor | reporting |

## 4. Peta Halaman Admin
| Halaman | Tujuan | Modul |
|---------|--------|-------|
| Dashboard Admin | ringkasan operasional | admin dashboard |
| User dan Role | user management, role, permission | security |
| Master Mahasiswa | CRUD dan validasi mahasiswa | master data |
| Master Dosen | CRUD dosen | master data |
| Struktur Akademik | fakultas, jurusan, prodi | master data |
| Kurikulum dan Mata Kuliah | referensi akademik | master data |
| Kelas dan Jadwal | pengaturan kelas, ruang, jadwal | operations |
| Approval Queue | semua approval lintas modul | governance |
| Verifikasi Data | pembanding perubahan dan bukti | governance |
| Pengumuman | publikasi informasi | administration |
| Kalender Akademik | periode dan agenda institusi | administration |
| Laporan | generate laporan dan ekspor | reporting |
| Audit Log | jejak perubahan sensitif | governance |
| Activity Log | aktivitas operasional | governance |

## 5. Detail Desain Halaman
### 5.1 Login
- Layout dua kolom di desktop: panel branding institusi dan form login.
- Menampilkan bantuan keamanan, pesan error jelas, dan status akun.
- Aksi sekunder: lupa password.

### 5.2 Dashboard Per Peran
- Bagian atas: salam, filter semester atau tahun akademik, quick action.
- Baris pertama: 4 sampai 6 kartu KPI.
- Baris kedua: grafik, status tugas, dan notifikasi proses.
- Konten bawah: tabel atau feed aktivitas sesuai peran.

### 5.3 KRS Online
- Panel kiri: daftar mata kuliah tersedia dengan filter kurikulum, kelas, hari, dosen.
- Panel kanan: keranjang KRS, total SKS, konflik jadwal, prasyarat, status administrasi.
- Header tetap menampilkan semester aktif dan sisa SKS.

### 5.4 Input Nilai Dosen
- Header kelas, jumlah mahasiswa, status draft atau final.
- Tabel nilai editable dengan komponen tugas, UTS, UAS, nilai akhir.
- Footer sticky berisi rata-rata kelas, jumlah mahasiswa belum lengkap, dan tombol finalisasi.

### 5.5 Approval Queue
- Default view berbentuk list prioritas.
- Tiap item menampilkan jenis approval, pemilik, SLA, dan status.
- Klik item membuka drawer pembanding data lama dan baru.

### 5.6 Audit Log
- Tabel log dengan filter modul, aksi, pelaku, rentang waktu.
- Drawer detail menampilkan old values dan new values.
- Bisa diurutkan berdasarkan waktu terbaru.

## 6. Responsivitas
- Desktop: sidebar permanen, tabel penuh, multi-column cards.
- Tablet landscape: sidebar collapse, filter menjadi off-canvas, tabel horizontal scroll.
- Tablet portrait: KPI menjadi dua kolom, panel kanan turun ke bawah.

## 7. Interaksi Utama
- Hover state jelas di semua row tabel, tombol, dan card actionable.
- Status badge selalu berwarna konsisten.
- Toast untuk feedback aksi berhasil atau gagal.
- Skeleton loader untuk tabel dan dashboard saat loading.

## 8. Aksesibilitas
- Kontras warna minimum WCAG AA untuk teks utama.
- Semua status tidak hanya dibedakan dengan warna, tetapi juga label teks.
- Fokus keyboard jelas untuk form, button, dan tabel interaktif.

## 9. Catatan Implementasi
- Flutter Web menggunakan shell layout dengan `go_router` nested route.
- Admin panel menggunakan Filament untuk percepatan CRUD dan Blade untuk halaman kustom bila perlu.
- Semua halaman mengikuti kontrak data dari REST API `/api/v1`.
