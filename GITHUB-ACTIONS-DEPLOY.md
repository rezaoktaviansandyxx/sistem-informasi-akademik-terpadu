# GitHub Actions Deployment

Dokumen ini menjelaskan workflow yang sudah disiapkan untuk repo ini:

- Frontend `Flutter Web` deploy ke `Cloudflare Pages`
- Backend `Laravel API` deploy ke `Railway`

## Workflow Yang Ditambahkan

- `.github/workflows/deploy-frontend-cloudflare.yml`
- `.github/workflows/deploy-backend-railway.yml`

Keduanya berjalan saat:

- `push` ke branch `main`
- dijalankan manual lewat `workflow_dispatch`

## 1. Frontend Flutter Web ke Cloudflare Pages

Workflow frontend:

1. checkout repository
2. setup `Flutter`
3. jalankan `flutter create .` untuk melengkapi file project yang masih skeleton
4. jalankan `flutter pub get`
5. jalankan `flutter test`
6. build `Flutter Web`
7. deploy folder `build/web` ke `Cloudflare Pages`

### GitHub Secrets yang wajib

- `CLOUDFLARE_API_TOKEN`
- `CLOUDFLARE_ACCOUNT_ID`

### GitHub Variables yang wajib

- `CLOUDFLARE_PAGES_PROJECT_NAME`
- `SIAT_API_BASE_URL`

### Contoh nilai variable frontend

```text
CLOUDFLARE_PAGES_PROJECT_NAME=siat-web
SIAT_API_BASE_URL=https://api.siat.example.com/api/v1
```

## 2. Backend Laravel API ke Railway

Workflow backend:

1. checkout repository
2. setup `PHP 8.3`
3. setup `Node.js 22`
4. validasi `composer.json`
5. install dependency `Composer`
6. siapkan `.env`
7. install dependency `npm`
8. jalankan `php artisan test`
9. build asset `Vite`
10. deploy folder `backend/siat_api` ke `Railway` lewat CLI

### GitHub Secret yang wajib

- `RAILWAY_TOKEN`

### GitHub Variables yang wajib

- `RAILWAY_PROJECT_ID`
- `RAILWAY_ENVIRONMENT_NAME`
- `RAILWAY_SERVICE_NAME`

### Contoh nilai variable backend

```text
RAILWAY_PROJECT_ID=your-project-id
RAILWAY_ENVIRONMENT_NAME=production
RAILWAY_SERVICE_NAME=siat-api
```

## 3. Variabel Produksi Yang Tetap Harus Diisi Di Railway

Di dashboard `Railway`, service Laravel tetap perlu environment variable aplikasi seperti:

```env
APP_NAME=SIAT
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.siat.example.com

DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

## 4. Cara Menyalakan Workflow

1. push semua perubahan ini ke GitHub
2. buka repository `GitHub`
3. masuk ke `Settings -> Secrets and variables -> Actions`
4. isi semua secret dan variable yang disebut di atas
5. push ke `main` atau jalankan manual dari tab `Actions`

## 5. Catatan Penting

- Workflow frontend mengasumsikan target deploy adalah `Cloudflare Pages`
- Workflow backend mengasumsikan target deploy adalah `Railway`
- Workflow backend deploy langsung dari subfolder `backend/siat_api`, jadi tidak perlu memisahkan repo
- Agar domain frontend bisa memanggil backend, pastikan CORS Laravel mengizinkan origin frontend Anda
- Migrasi database produksi belum dijalankan otomatis di workflow ini agar lebih aman untuk tahap awal

## 6. Langkah Setelah Ini

Setelah workflow aktif, biasanya langkah berikutnya adalah:

1. sambungkan custom domain frontend di `Cloudflare Pages`
2. generate domain backend di `Railway`
3. isi `SIAT_API_BASE_URL` dengan URL backend final
4. tambahkan migrasi otomatis atau job release jika Anda ingin deploy backend lebih penuh otomatis
