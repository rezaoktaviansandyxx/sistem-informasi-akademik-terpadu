# Frontend SIAT Web

Folder ini berisi source skeleton Flutter Web untuk SIAT:

- `lib/core`: theme, router, auth session, network
- `lib/features`: auth, dashboard, academic, lecturer, profile
- `lib/shared`: layout shell
- `test`: smoke test awal

## Catatan

Environment kerja saat ini belum memiliki `flutter`, sehingga scaffold resmi `flutter create` belum bisa dijalankan.

Saat runtime tersedia, gunakan salah satu pendekatan berikut:

1. Jalankan `flutter create .` di folder ini lalu pertahankan file source yang sudah ada.
2. Atau buat project Flutter baru, lalu salin isi `lib/` dan `test/` dari folder ini.

## Dependency

- `flutter_riverpod`
- `go_router`
- `dio`
- `intl`
