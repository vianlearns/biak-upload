<?php
date_default_timezone_set('Asia/Jakarta');
// Konfigurasi Database
$host = 'localhost';
$dbname = 'biak_upload';
$username = 'root';
$password = '';

// Maksimal ukuran file (500MB)
define('MAX_FILE_SIZE', 500 * 1024 * 1024);

// Direktori upload
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Base URL (sesuaikan dengan domain Anda)
define('BASE_URL', 'http://localhost/');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Use JSON error response for AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    } else {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}

// Fungsi untuk mendapatkan ID urutan berikutnya
function getNextIdUrutan($pdo) {
    try {
        $pdo->beginTransaction();
        
        // Lock row untuk prevent race condition
        $stmt = $pdo->query("SELECT last_id_urutan FROM counter WHERE id = 1 FOR UPDATE");
        $counter = $stmt->fetch();
        
        if (!$counter) {
            $lastId = 0;
            $pdo->exec("INSERT INTO counter (id, last_id_urutan) VALUES (1, 1)");
        } else {
            $lastId = $counter['last_id_urutan'];
            $newId = $lastId + 1;
            $pdo->exec("UPDATE counter SET last_id_urutan = $newId WHERE id = 1");
        }
        
        $pdo->commit();
        return $lastId + 1;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Fungsi untuk membersihkan file yang sudah kadaluarsa
function cleanupExpiredFiles($pdo) {
    try {
        $now = date('Y-m-d H:i:s');
        
        // Ambil file yang sudah kadaluarsa
        $stmt = $pdo->prepare("SELECT * FROM uploads WHERE waktu_hapus <= ?");
        $stmt->execute([$now]);
        $expiredFiles = $stmt->fetchAll();
        
        $deletedCount = 0;
        foreach ($expiredFiles as $file) {
            $filePath = UPLOAD_DIR . $file['nama_file'];
            if (file_exists($filePath)) {
                if (@unlink($filePath)) {
                    // Hapus dari database hanya jika file berhasil dihapus
                    $stmt = $pdo->prepare("DELETE FROM uploads WHERE id = ?");
                    $stmt->execute([$file['id']]);
                    $deletedCount++;
                }
            } else {
                // File tidak ada, hapus record dari database
                $stmt = $pdo->prepare("DELETE FROM uploads WHERE id = ?");
                $stmt->execute([$file['id']]);
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    } catch (Exception $e) {
        error_log("Cleanup error: " . $e->getMessage());
        return 0;
    }
}

// Jalankan cleanup setiap kali ada request (tapi jangan ganggu AJAX requests)
if (basename($_SERVER['PHP_SELF']) !== 'cleanup.php' && 
    basename($_SERVER['PHP_SELF']) !== 'upload.php' &&
    (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')) {
    try {
        cleanupExpiredFiles($pdo);
    } catch (Exception $e) {
        // Silent error untuk cleanup otomatis
        error_log("Auto cleanup error: " . $e->getMessage());
    }
}