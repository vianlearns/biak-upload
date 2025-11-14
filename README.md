# Web Upload Sementara - Biro Akademik UDINUS

Website sederhana untuk upload file sementara tanpa kompresi, khusus untuk keperluan Biro Akademik UDINUS.

## Fitur Utama

### Untuk Mahasiswa
- ✅ Upload file tanpa kompresi (maksimal 500MB per file)
- ✅ Multiple file upload sekaligus
- ✅ Drag & drop file atau klik untuk memilih
- ✅ Pilihan waktu penghapusan otomatis (15 menit - 1 hari)
- ✅ Tampilan progress bar saat upload
- ✅ Link otomatis untuk dibagikan ke staf biro
- ✅ Support semua jenis file (gambar, PDF, video, dokumen, zip, dll)

### Untuk Biro Akademik
- ✅ Akses file tanpa login
- ✅ Tampilan informasi file lengkap (nama, ukuran, waktu upload)
- ✅ Download file per satu atau semua sekaligus
- ✅ Indikator waktu tersisa sebelum file dihapus
- ✅ Tampilan mobile-friendly

## Persyaratan Sistem

- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Apache/Nginx web server
- Extension PHP: PDO, PDO_MySQL, fileinfo

## Instalasi

### 1. Setup Database
1. Buat database MySQL baru
2. Import file `database.sql` ke database Anda
3. Edit file `config.php` dan sesuaikan koneksi database:
```php
$host = 'localhost';
$dbname = 'biak_upload';
$username = 'root'; // sesuaikan
$password = ''; // sesuaikan
```

### 2. Setup Folder Upload
Pastikan folder `uploads` memiliki permission yang tepat:
```bash
chmod 755 uploads/
```

### 3. Konfigurasi Web Server

#### Untuk Apache
File `.htaccess` sudah disediakan dengan konfigurasi optimal.

#### Untuk Nginx
Tambahkan konfigurasi berikut:
```nginx
location / {
    try_files $uri $uri/ /view.php?id=$uri&$args;
}

location ~ /uploads/ {
    deny all;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

### 4. Konfigurasi PHP
Pastikan pengaturan PHP berikut:
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 0
max_input_time = 0
memory_limit = 512M
```

## Penggunaan

### Untuk Mahasiswa
1. Buka halaman utama (index.php)
2. Pilih file yang ingin diupload (bisa drag & drop)
3. Pilih waktu penghapusan otomatis
4. Klik "Upload File"
5. Salin link yang muncul dan bagikan ke staf biro

### Untuk Staf Biro Akademik
1. Buka link yang diberikan mahasiswa (contoh: `yourdomain.com/123`)
2. Lihat daftar file yang diupload
3. Klik "Download" untuk file tertentu atau "Download Semua"
4. File akan otomatis terhapus sesuai waktu yang dipilih mahasiswa

## Setup Cron Job (Opsional)
Untuk penghapusan otomatis yang lebih konsisten, tambahkan cron job:
```bash
# Jalankan cleanup setiap 15 menit
*/15 * * * * /usr/bin/php /path/to/your/website/cleanup.php >> /var/log/upload-cleanup.log 2>&1
```

## Keamanan

- File disimpan di folder terpisah dengan nama acak
- Tidak ada eksekusi file di folder uploads
- Validasi tipe file dan ukuran
- IP address tracking untuk setiap upload
- Penghapusan otomatis file kadaluarsa
- Proteksi terhadap file sensitif (SQL, log, config)

## Troubleshooting

### Upload gagal untuk file besar
- Periksa konfigurasi PHP `upload_max_filesize` dan `post_max_size`
- Pastikan tidak ada batasan di web server (client_max_body_size untuk Nginx)

### File tidak bisa didownload
- Periksa permission folder `uploads`
- Pastikan file masih ada dan belum kadaluarsa

### Database connection error
- Periksa kredensial database di `config.php`
- Pastikan MySQL service berjalan

## Kontak

Untuk pertanyaan atau masalah, hubungi tim IT Biro Akademik UDINUS.

---

**Catatan:** Website ini dirancang untuk keperluan sementara. File akan otomatis terhapus sesuai waktu yang dipilih pengguna.