# 🚀 Panduan Instalasi Stralentech.ai

## 📋 Persyaratan Sistem
- **Web Server**: Apache/Nginx dengan PHP 7.4+
- **Database**: MySQL 5.7+ atau MariaDB 10.3+
- **PHP Extensions**: mysqli, fileinfo, session
- **Storage**: Minimal 100MB untuk uploads

## 🛠️ Langkah Instalasi

### 1. Upload File ke CPanel
1. Extract file `stralentech-ai-website.zip`
2. Upload semua file ke folder `public_html` di CPanel
3. Pastikan struktur folder seperti ini:
   ```
   public_html/
   ├── index.php
   ├── demo.php
   ├── register.php
   ├── login.php
   ├── member.php
   ├── admin.php
   ├── includes/
   ├── assets/
   ├── uploads/
   └── sql/
   ```

### 2. Setup Database
1. Buka **phpMyAdmin** di CPanel
2. Buat database baru (contoh: `stralentech_ai`)
3. Import file `sql/structure.sql` ke database tersebut
4. Catat informasi database:
   - Host: `localhost`
   - Username: `your_db_username`
   - Password: `your_db_password`
   - Database: `stralentech_ai`

### 3. Konfigurasi Environment
1. Edit file `.env.php`
2. Update kredensial database:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_db_username');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'stralentech_ai');
   ```

### 4. Set Permission Folder
1. Set permission folder `uploads/` ke **755** atau **777**
2. Pastikan web server bisa menulis ke folder tersebut

### 5. Test Website
1. Akses website Anda: `https://yourdomain.com`
2. Untuk demo tanpa database: `https://yourdomain.com/demo.php`

## 🔐 Akun Default

### Admin
- **Email**: admin@stralentech.ai
- **Password**: admin123

### Member
- Daftar akun baru melalui halaman registrasi

## 🎯 Fitur Utama

### ✅ Homepage
- Grid video responsif
- Filter kategori
- Search functionality
- Video labels (Best of Week, Top of Month, Sponsor)

### ✅ User Management
- Registrasi & login
- Role-based access (Member/Admin)
- Premium membership system

### ✅ Video System
- Upload MP4 (max 50MB, max 1 menit)
- Approval workflow
- Like & comment system
- Daily upload limits

### ✅ Admin Panel
- User management
- Video moderation
- Category management
- Statistics dashboard

## 🔧 Troubleshooting

### Error: mysqli_connect() not found
- Pastikan PHP extension `mysqli` aktif
- Hubungi hosting provider untuk mengaktifkan

### Error: Permission denied pada uploads/
- Set permission folder `uploads/` ke 755 atau 777
- Pastikan ownership folder sesuai dengan web server

### Error: Database connection failed
- Periksa kredensial database di `.env.php`
- Pastikan database sudah dibuat dan diimport

### Video tidak bisa diupload
- Periksa permission folder `uploads/`
- Pastikan ukuran file < 50MB
- Pastikan format file adalah MP4

## 📞 Support

Jika mengalami kesulitan instalasi:
1. Periksa file `README.md` untuk dokumentasi lengkap
2. Pastikan semua persyaratan sistem terpenuhi
3. Hubungi hosting provider untuk bantuan teknis

## 🎉 Selamat!

Website Stralentech.ai siap digunakan! 
Akses `/demo.php` untuk melihat preview UI tanpa database.
