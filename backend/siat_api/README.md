# Backend SIAT API

Backend ini adalah proyek Laravel 13 untuk Sistem Informasi Akademik Terpadu.

## Cakupan Yang Sudah Disiapkan

- Routing API versi `v1` di `routes/api.php`
- Auth endpoint berbasis Sanctum
- Dashboard multi-role
- Endpoint KRS mahasiswa
- Endpoint input dan finalisasi nilai dosen
- Approval workflow dasar
- Layer `Request -> Controller -> Service -> Repository`
- Migration untuk identity, akademik, dan governance
- Seeder role dan permission
- Dashboard admin Blade sederhana di `/admin`

## Database

Target database backend ini adalah **MySQL atau MariaDB** yang dapat dikelola lewat **phpMyAdmin**, bukan PostgreSQL.

Contoh `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siat_api
DB_USERNAME=root
DB_PASSWORD=
```

## Langkah Menjalankan

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Akun Seed Awal

- Admin: `admin@siat.local` / `password123`
- Dosen: `lecturer@siat.local` / `password123`
- Mahasiswa: `student@siat.local` / `password123`

## Catatan

- Environment kerja agent saat ini belum memiliki `php` aktif di PATH, jadi instalasi dependency dan eksekusi artisan belum bisa dijalankan langsung dari sesi ini.
- Struktur kode sudah disesuaikan agar mengikuti proyek Laravel 13 yang sudah Anda buat di folder ini.
