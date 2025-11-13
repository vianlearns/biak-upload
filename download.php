<?php
require_once __DIR__ . '/config.php';

// Mendapatkan nama file dari URL
 $fileName = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($fileName)) {
    header('Location: index.php');
    exit;
}

// Verifikasi file ada di database
 $stmt = $conn->prepare("SELECT * FROM uploads WHERE nama_file = ?");
 $stmt->bind_param("s", $fileName);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

 $fileData = $result->fetch_assoc();
 $stmt->close();
 $conn->close();

// Cek kedaluwarsa menggunakan waktu dari PHP untuk menghindari perbedaan timezone
$expiresAt = strtotime($fileData['waktu_hapus']);
if ($expiresAt !== false && $expiresAt <= time()) {
    header('Location: index.php');
    exit;
}

// Path file
 $filePath = __DIR__ . '/uploads/' . $fileName;

// Periksa apakah file ada
if (!file_exists($filePath)) {
    header('Location: index.php');
    exit;
}

// Mendapatkan nama file asli (tanpa timestamp)
 $originalName = getOriginalFileName($fileName);

// Set header untuk download
header('Content-Description: File Transfer');
header('Content-Type: ' . $fileData['jenis_berkas']);
header('Content-Disposition: attachment; filename="' . $originalName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Baca file dan output ke browser
readfile($filePath);
exit;

// Fungsi helper untuk mendapatkan nama file asli
function getOriginalFileName($fileName) {
    // Format nama file: timestamp_nama_asli
    $parts = explode('_', $fileName, 2);
    if (count($parts) >= 2) {
        return $parts[1];
    }
    return $fileName;
}
?>