<?php
// Disable error reporting to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config.php';

// Set unlimited execution time untuk upload besar
set_time_limit(0);

// Set memory limit
ini_set('memory_limit', '512M');

// Start output buffering to prevent any unwanted output
ob_start();

header('Content-Type: application/json');

try {
    // Validasi request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode request tidak valid');
    }

    // Validasi file upload
    if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
        throw new Exception('Tidak ada file yang diupload');
    }

    // Validasi waktu hapus
    if (!isset($_POST['deleteTime']) || !is_numeric($_POST['deleteTime'])) {
        throw new Exception('Waktu penghapusan tidak valid');
    }

    $deleteTime = intval($_POST['deleteTime']);
    $uploadedFiles = [];
    $idUrutan = getNextIdUrutan($pdo);
    $uploadTime = date('Y-m-d H:i:s');
    $deleteTimeDate = date('Y-m-d H:i:s', strtotime("+$deleteTime minutes"));
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    // Proses setiap file
    $fileCount = count($_FILES['files']['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = $_FILES['files']['name'][$i];
        $fileTmpName = $_FILES['files']['tmp_name'][$i];
        $fileSize = $_FILES['files']['size'][$i];
        $fileError = $_FILES['files']['error'][$i];
        $fileType = $_FILES['files']['type'][$i];

        // Validasi error
        if ($fileError !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi batas PHP)',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi batas form)',
                UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak tersedia',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file',
                UPLOAD_ERR_EXTENSION => 'Upload dibatalkan oleh ekstensi PHP'
            ];
            throw new Exception("Error pada file $fileName: " . ($errorMessages[$fileError] ?? 'Unknown error'));
        }

        // Validasi ukuran file (500MB)
        if ($fileSize > MAX_FILE_SIZE) {
            throw new Exception("File $fileName terlalu besar. Maksimal 500MB");
        }

        // Generate nama file unik dengan nama asli
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        
        // Bersihkan nama file
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
        $baseName = substr($baseName, 0, 100); // Batasi panjang nama
        
        // Generate nama file dengan timestamp untuk menghindari duplikasi
        $uniqueFileName = $baseName . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
        
        // Validasi direktori upload
        if (!is_dir(UPLOAD_DIR)) {
            if (!mkdir(UPLOAD_DIR, 0755, true)) {
                throw new Exception('Gagal membuat direktori upload');
            }
        }

        // Validasi writable
        if (!is_writable(UPLOAD_DIR)) {
            throw new Exception('Direktori upload tidak dapat ditulis');
        }

        // Pindahkan file
        $uploadPath = UPLOAD_DIR . $uniqueFileName;
        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            throw new Exception("Gagal memindahkan file $fileName");
        }

        // Simpan ke database
        $stmt = $pdo->prepare("INSERT INTO uploads (id_urutan, nama_file, nama_asli, jenis_file, ukuran_file, waktu_upload, waktu_hapus, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$idUrutan, $uniqueFileName, $fileName, $fileType, $fileSize, $uploadTime, $deleteTimeDate, $ipAddress]);

        $uploadedFiles[] = [
            'original_name' => $fileName,
            'saved_name' => $uniqueFileName,
            'size' => $fileSize,
            'type' => $fileType
        ];
    }

    // Response sukses
    echo json_encode([
        'status' => 'success',
        'id' => $idUrutan,
        'message' => 'Upload berhasil',
        'files' => $uploadedFiles,
        'delete_time' => $deleteTimeDate,
        'upload_time' => $uploadTime
    ]);

} catch (Exception $e) {
    // Hapus file yang sudah terupload jika ada error
    if (isset($uploadedFiles)) {
        foreach ($uploadedFiles as $file) {
            $filePath = UPLOAD_DIR . $file['saved_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Clean output buffer
ob_end_flush();