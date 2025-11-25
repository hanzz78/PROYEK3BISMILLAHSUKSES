📦 Polban DataHub
Portal Input & Integrasi Data Kampus untuk Polban Dataverse

Polban DataHub adalah backend utama untuk mengumpulkan, memvalidasi, dan mengelola data dari berbagai unit di Politeknik Negeri Bandung.
Sistem ini menjadi fondasi data sebelum diolah oleh Polban DataCore dan divisualisasikan di Polban DataView.
DataHub menyediakan antarmuka input berbasis web dan REST API yang dapat digunakan oleh unit kampus (participant) untuk mengirimkan data terstruktur ke dalam sistem secara aman dan konsisten.

👥 Tim Pengembang
Anggota Tim DataHub :
- Ersya Hasby Satria - 241511072 - Frontend
- Alda Pujama - 241511066 - Backend
- Muhammad Raihan Abubakar - 241511084 - Backend
- Alexandrio Vega Bonito - 241511067 - Database
- Gema Adzan Firdaus - 241511075 - Database


🚀 Fungsi Utama
DataHub bertugas sebagai gerbang masuk seluruh data kampus.
Sistem mencakup:
✔️ Manajemen Pengguna & Role
Admin
Participant (unit/prodi/dosen)
Viewer internal (opsional)

✔️ Form Input Data
Input manual lewat form web
Import file CSV/Excel (opsional bila fitur dikembangkan)

✔️ CRUD Kategori & Sub-Kategori
Admin dapat:
Menambah kategori baru
Mengatur sub-kategori
Menyetujui data dari participant

✔️ REST API Internal
Digunakan oleh:
DataCore → untuk mengambil data mentah dan membuat agregasi
Unit internal → integrasi jika ingin push data otomatis

✔️ Keamanan & Validasi
Validasi format input
Pembatasan akses berdasarkan role
Logging aktivitas pengguna


🧭 Hubungan dengan Sistem Lain
🟦 DataHub → DataCore
Menyediakan API /api/data/... untuk mengambil data mentah
DataCore melakukan agregasi, statistik, dan pembersihan data
DataHub hanya menyimpan data asli tanpa mengolah insight

🟩 DataCore → DataView
Setelah data diolah, DataCore menyediakan data agregat untuk DataView
DataHub tidak berhubungan langsung dengan DataView

🔗 Diagram Alur Sederhana
Participant → DataHub → DataCore → DataView (Dashboard)

🏗️ Teknologi yang Digunakan
Backend
Laravel 12
REST API

Frontend
Vue 3 + Vite
Tailwind CSS
Axios

Database
PostgreSQL

Lainnya
Git & GitHub Workflow
Composer & NPM

datahub-backend/
├─ app/
├─ routes/
│  ├─ web.php
│  ├─ api.php
├─ public/
│  ├─ build/        ← hasil Vite build ada di sini
│  ├─ logo.png
├─ resources/
│  ├─ js/
│  │  ├─ pages/
│  │  ├─ components/
│  ├─ views/
│  │  └─ app.blade.php
├─ vite.config.js
└─ README.md

⚙️ Cara Menjalankan Proyek (Development)
1️⃣ Install Dependency
Backend
composer install
Frontend
npm install

2️⃣ Copy Environment
cp .env.example .env
Sesuaikan database dan konfigurasi Vite.

3️⃣ Generate Application Key
php artisan key:generate

4️⃣ Migrasi Database
php artisan migrate

5️⃣ Jalankan Backend
php artisan serve

6️⃣ Jalankan Frontend (HMR)
npm run dev

📦 Build for Production
npm run build


Hasil build akan muncul di:
public/build
Jika muncul error ViteManifestNotFoundException, pastikan:
npm run build sudah berjalan sukses
public/build/manifest.json sudah ada


🔐 Role & Akses
Admin	Kelola user, kategori, verifikasi data
Participant	Input data, upload CSV
Viewer (opsional)	Melihat data terdaftar
