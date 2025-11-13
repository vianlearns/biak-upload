<?php
require_once __DIR__ . '/config.php';

// Query untuk mendapatkan file yang sudah kedaluwarsa
$result = $conn->query("SELECT * FROM uploads WHERE waktu_hapus < NOW()");

 $deletedFiles = 0;
 $deletedRecords = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $filePath = __DIR__ . '/uploads/' . $row['nama_file'];
        
        // Hapus file jika ada
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                $deletedFiles++;
            }
        }
        
        // Hapus record dari database
        $stmt = $conn->prepare("DELETE FROM uploads WHERE id = ?");
        $stmt->bind_param("i", $row['id']);
        if ($stmt->execute()) {
            $deletedRecords++;
        }
        $stmt->close();
    }
}

// Log hasil penghapusan (opsional)
$logMessage = date('Y-m-d H:i:s') . " - Deleted {$deletedFiles} files and {$deletedRecords} records\n";
file_put_contents(__DIR__ . '/delete_log.txt', $logMessage, FILE_APPEND);

 $conn->close();
?>