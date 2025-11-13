<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Mendapatkan ID urutan berikutnya
    $result = $conn->query("SELECT MAX(id_urutan) as max_id FROM uploads");
    $row = $result->fetch_assoc();
    $next_id = (($row['max_id'] ?? 0) + 1);
    
    // Mendapatkan waktu hapus
    $deleteTimeMinutes = isset($_POST['deleteTime']) ? (int)$_POST['deleteTime'] : 60;
    $deleteTime = date('Y-m-d H:i:s', strtotime("+$deleteTimeMinutes minutes"));
    $uploadTime = date('Y-m-d H:i:s');
    
    // Memastikan folder uploads ada
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    $uploadedFiles = [];
    $errors = [];
    $totalRequested = 0;
    
    // Proses setiap file
    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        $filesCount = count($_FILES['files']['name']);
        $totalRequested = $filesCount;
        
        for ($i = 0; $i < $filesCount; $i++) {
            $fileName = $_FILES['files']['name'][$i];
            $fileTmpName = $_FILES['files']['tmp_name'][$i];
            $fileSize = $_FILES['files']['size'][$i];
            $fileType = $_FILES['files']['type'][$i];
            $fileError = $_FILES['files']['error'][$i];
            if ($fileError !== UPLOAD_ERR_OK) {
                $errors[] = "Gagal mengunggah file $fileName";
                continue;
            }
            $sanitizedName = preg_replace('/[^\pL\pN\.\-_ ]/u', '_', $fileName);
            $sanitizedName = trim($sanitizedName);
            if ($sanitizedName === '') { $sanitizedName = 'file'; }
            $uniqueFileName = time() . '_' . $sanitizedName;
            $uploadPath = 'uploads/' . $uniqueFileName;
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                $stmt = $conn->prepare("INSERT INTO uploads (id_urutan, nama_file, jenis_berkas, ukuran_file, waktu_upload, waktu_hapus) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ississ", $next_id, $uniqueFileName, $fileType, $fileSize, $uploadTime, $deleteTime);
                if ($stmt->execute()) {
                    $uploadedFiles[] = [
                        'name' => $sanitizedName,
                        'path' => $uploadPath,
                        'type' => $fileType,
                        'size' => $fileSize
                    ];
                } else {
                    $errors[] = "Gagal menyimpan informasi file $sanitizedName ke database";
                    unlink($uploadPath);
                }
                $stmt->close();
            } else {
                $errors[] = "Gagal mengunggah file $sanitizedName";
            }
        }
    }

    if ($totalRequested === 0 || count($uploadedFiles) === 0) {
        $errors[] = 'Tidak ada file yang diunggah';
    }
    
    if (!empty($errors)) {
        echo json_encode([
            'status' => 'error',
            'message' => implode(', ', $errors)
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'id' => $next_id,
            'saved_count' => count($uploadedFiles),
            'saved_files' => array_map(function($f){ return $f['name']; }, $uploadedFiles),
            'message' => 'File berhasil diunggah'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
