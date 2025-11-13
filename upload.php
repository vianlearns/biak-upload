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
    
    // Proses setiap file
    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        $filesCount = count($_FILES['files']['name']);
        
        for ($i = 0; $i < $filesCount; $i++) {
            $fileName = $_FILES['files']['name'][$i];
            $fileTmpName = $_FILES['files']['tmp_name'][$i];
            $fileSize = $_FILES['files']['size'][$i];
            $fileType = $_FILES['files']['type'][$i];
            
            // Generate nama file unik untuk menghindari duplikasi
            $uniqueFileName = time() . '_' . $fileName;
            $uploadPath = 'uploads/' . $uniqueFileName;
            
            // Pindahkan file ke folder uploads
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                // Simpan informasi file ke database
                $stmt = $conn->prepare("INSERT INTO uploads (id_urutan, nama_file, jenis_berkas, ukuran_file, waktu_upload, waktu_hapus) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ississ", $next_id, $uniqueFileName, $fileType, $fileSize, $uploadTime, $deleteTime);
                
                if ($stmt->execute()) {
                    $uploadedFiles[] = [
                        'name' => $fileName,
                        'path' => $uploadPath,
                        'type' => $fileType,
                        'size' => $fileSize
                    ];
                } else {
                    $errors[] = "Gagal menyimpan informasi file $fileName ke database";
                    // Hapus file yang sudah diupload jika gagal menyimpan ke database
                    unlink($uploadPath);
                }
                
                $stmt->close();
            } else {
                $errors[] = "Gagal mengunggah file $fileName";
            }
        }
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