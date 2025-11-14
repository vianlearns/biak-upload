<?php
require_once 'config.php';

// Ambil ID file dari parameter
$fileId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($fileId <= 0) {
    die("ID file tidak valid");
}

try {
    $stmt = $pdo->prepare("SELECT * FROM uploads WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch();

    if (!$file) {
        die("File tidak ditemukan atau sudah kadaluarsa");
    }

    $waktuHapusTs = strtotime($file['waktu_hapus']);
    if ($waktuHapusTs !== false && $waktuHapusTs <= time()) {
        die("File tidak ditemukan atau sudah kadaluarsa");
    }

    $filePath = UPLOAD_DIR . $file['nama_file'];
    $originalName = $file['nama_asli'];

    // Validasi file exists
    if (!file_exists($filePath)) {
        die("File tidak ditemukan di server");
    }

    // Set headers untuk download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $originalName . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Bersihkan output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Read file dengan chunk untuk file besar
    $chunkSize = 1024 * 1024; // 1MB chunk
    $handle = fopen($filePath, 'rb');
    
    if ($handle === false) {
        die("Gagal membuka file");
    }
    
    while (!feof($handle)) {
        echo fread($handle, $chunkSize);
        flush();
    }
    
    fclose($handle);
    exit;

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}