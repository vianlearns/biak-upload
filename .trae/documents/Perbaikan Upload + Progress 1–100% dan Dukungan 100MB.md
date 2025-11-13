## Ringkasan Temuan
- Upload saat ini memakai `fetch` tanpa progres nyata; UI hanya menampilkan spinner dan progress bar statis (index.php:260–311).
- Handler server `upload.php` memproses multi-file sekaligus, menyimpan metadata ke DB, namun tidak memeriksa `$_FILES['error']`, tidak ada validasi tipe, dan mengandalkan `$_FILES['type']` untuk MIME.
- Tidak ada konfigurasi batas upload (`upload_max_filesize`, `post_max_size`) di repo; mengikuti default PHP/XAMPP.
- Folder `uploads/` berada di bawah webroot tanpa proteksi eksekusi skrip.

## Target
1. Menambahkan progres upload nyata 0–100% saat unggah.
2. Memastikan dukungan upload berkas besar hingga 100MB dengan pengaturan server yang tepat.
3. Meningkatkan robustnes dan keamanan dasar proses upload.

## Perubahan Frontend (index.php)
1. Ganti `fetch('upload.php', ...)` dengan `XMLHttpRequest` agar dapat memanfaatkan `xhr.upload.onprogress`.
2. Hitung progres total berdasarkan `e.loaded/e.total` dan distribusikan ke progress bar per-file menggunakan ukuran masing-masing file (agregasi satu request):
   - Hitung `totalSize = sum(file.size)`.
   - Pada event progres, proyeksikan byte terunggah ke tiap file untuk memperbarui `progress-bar-{index}` dan `progress-percent-{index}`.
3. Hapus delay 10 detik; tampilkan hasil segera saat response sukses.
4. Pertahankan overlay/loading hanya selama upload berlangsung; pastikan ditutup pada success/error.
5. Tangani error network/JSON secara eksplisit; tampilkan notifikasi dan reset UI konsisten.

## Perubahan Backend (upload.php)
1. Periksa `$_FILES['files']['error'][$i]` dan beri pesan jelas jika ada kesalahan (ukuran melebihi batas, partial upload, dsb.).
2. Sanitasi nama file (hapus karakter berbahaya), simpan nama unik tetap (`time()_originalName`).
3. Validasi tipe berkas berbasis ekstensi whitelist (mis. dokumen, gambar umum); tolak tipe berisiko (opsional sesuai kebutuhan Anda).
4. Tentukan MIME saat download menggunakan `finfo` daripada mengandalkan `$_FILES['type']` (perbaikan di `download.php`).
5. Pertahankan satu `id_urutan` untuk semua file dalam satu request; tidak mengubah skema saat ini.

## Konfigurasi Server untuk 100MB
- Tambahkan `.htaccess` di root `htdocs` untuk menetapkan batas dan waktu:
  - `php_value upload_max_filesize 100M`
  - `php_value post_max_size 120M`
  - `php_value max_execution_time 300`
  - `php_value max_input_time 300`
- Alternatif (jika `.htaccess` tidak aktif): update `php.ini` XAMPP dengan nilai yang sama dan restart Apache.

## Proteksi Folder Upload
- Tambahkan `.htaccess` di `uploads/` untuk mencegah eksekusi skrip:
  - `Options -Indexes`
  - `php_flag engine off` (untuk mod_php)
  - Hilangkan handler tipe skrip: `RemoveHandler .php .phtml .php3` dan `RemoveType .php .phtml .php3`.

## Verifikasi & Pengujian
1. Uji unggah beberapa file kecil dan satu file besar (~100MB); pastikan progres 0–100% berjalan halus dan hasil muncul segera.
2. Konfirmasi jumlah file tersimpan (`saved_count`) cocok dengan yang diunggah dan link akses `view.php?id=<id>` berfungsi.
3. Uji unduh (`download.php`) dengan MIME yang tepat.
4. Uji penanganan error: unggah file di atas 100MB atau non-whitelist; pastikan pesan error jelas.

## File yang Akan Diubah
- `index.php` (bagian JS upload: sekitar 260–311).
- `upload.php` (penanganan error, sanitasi/validasi minimal).
- `download.php` (penentuan MIME via `finfo`).
- Tambah `.htaccess` di `htdocs` dan `uploads/` untuk batas 100MB dan proteksi.

Jika disetujui, saya akan menerapkan perubahan di atas, memverifikasi dengan pengujian lokal, dan memastikan semuanya berjalan tanpa masalah.