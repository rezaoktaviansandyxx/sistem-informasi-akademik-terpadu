# SIAT Monorepo

Repositori ini menurunkan PRD SIAT menjadi fondasi implementasi:

- `frontend/siat_web`: aplikasi Flutter Web untuk mahasiswa, dosen, dan pimpinan.
- `backend/siat_api`: backend Laravel 13 untuk REST API `/api/v1`, admin dashboard Blade, RBAC, dan audit trail dengan MySQL atau MariaDB via phpMyAdmin.
- `.trae/documents`: dokumen PRD, arsitektur teknis, dan desain halaman.

## Status

Fondasi kode sudah disiapkan, tetapi environment saat ini belum memiliki runtime berikut:

- `php`
- `flutter`

Karena itu, proses `composer install`, `php artisan`, `flutter pub get`, dan `flutter run -d chrome` belum bisa dieksekusi langsung di workspace ini.

## Struktur

```text
.
├─ frontend/
│  └─ siat_web/
├─ backend/
│  └─ siat_api/
└─ .trae/
   └─ documents/
```

## Langkah Menjalankan Nanti

### Frontend Flutter Web

```bash
cd frontend/siat_web
flutter pub get
flutter run -d chrome
```

### Backend Laravel API

```bash
cd backend/siat_api
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Modul Yang Sudah Diturunkan

- Auth, session, role context, dan route guard Flutter.
- Dashboard multi-role untuk mahasiswa, dosen, admin, dan pimpinan.
- Halaman KRS, input nilai dosen, dan profil pengguna.
- Struktur Laravel API V1 untuk auth, dashboard, KRS, nilai dosen, dan approval.
- Service layer, repository contract, migration inti, seeder role-permission, dan Blade admin dashboard.
