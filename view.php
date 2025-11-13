<?php
require_once 'config.php';

// Mendapatkan ID dari URL
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Query untuk mendapatkan data file berdasarkan ID
 $stmt = $conn->prepare("SELECT * FROM uploads WHERE id_urutan = ? ORDER BY id ASC");
 $stmt->bind_param("i", $id);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

 $files = [];
 $deleteTime = null;
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
    $deleteTime = $row['waktu_hapus'];
}

 $stmt->close();
 $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkas #<?php echo $id; ?> - Biro Akademik UDINUS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .file-item {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <header class="text-center mb-10">
            <h1 class="text-3xl font-bold text-blue-800 mb-2">Berkas #<?php echo $id; ?></h1>
            <p class="text-gray-600">Biro Akademik UDINUS</p>
        </header>

        <main class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-blue-800">Informasi Berkas</h2>
                            <p class="text-sm text-gray-600 mt-1">
                                <i class="fas fa-clock mr-1"></i>
                                Diunggah pada: <?php echo date('d M Y H:i', strtotime($files[0]['waktu_upload'])); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Waktu tersisa:</p>
                            <p class="text-lg font-semibold text-red-600" id="countdown"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Daftar File (<?php echo count($files); ?>)</h3>
                    <button id="downloadAllBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                        <i class="fas fa-download mr-2"></i>Unduh Semua
                    </button>
                </div>

                <div class="space-y-2" id="fileList">
                    <?php foreach ($files as $file): ?>
                        <div class="file-item flex items-center justify-between p-3 bg-gray-50 rounded hover:bg-gray-100 transition">
                            <div class="flex items-center">
                                <i class="<?php echo getFileIcon($file['jenis_berkas']); ?> mr-3 text-gray-600 text-xl"></i>
                                <div>
                                    <p class="font-medium"><?php echo getOriginalFileName($file['nama_file']); ?></p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo $file['jenis_berkas']; ?> � <?php echo formatFileSize($file['ukuran_file']); ?>
                                    </p>
                                </div>
                            </div>
                            <a href="download.php?file=<?php echo urlencode($file['nama_file']); ?>" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="text-center">
                <a href="index.php" class="text-blue-600 hover:text-blue-800 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Halaman Upload
                </a>
            </div>
        </main>

        <footer class="text-center mt-8 text-gray-600 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Biro Akademik UDINUS - Sistem Upload Sementara</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countdownEl = document.getElementById('countdown');
            const downloadAllBtn = document.getElementById('downloadAllBtn');
            const deleteTime = <?php echo (int)(strtotime($deleteTime) * 1000); ?>;
            
            // Update countdown setiap detik
            const countdownInterval = setInterval(function() {
                const now = new Date().getTime();
                const distance = deleteTime - now;
                
                if (distance < 0) {
                    clearInterval(countdownInterval);
                    countdownEl.textContent = "Kedaluwarsa";
                    countdownEl.className = "text-lg font-semibold text-gray-500";
                    
                    // Nonaktifkan tombol download
                    downloadAllBtn.disabled = true;
                    downloadAllBtn.className = "bg-gray-400 text-white px-4 py-2 rounded cursor-not-allowed";
                    downloadAllBtn.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Berkas Kedaluwarsa';
                    
                    // Nonaktifkan semua tombol download individual
                    document.querySelectorAll('#fileList a').forEach(link => {
                        link.removeAttribute('href');
                        link.className = "bg-gray-400 text-white px-3 py-1 rounded cursor-not-allowed";
                        link.innerHTML = '<i class="fas fa-times"></i>';
                    });
                    
                    return;
                }
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                let countdownText = '';
                if (days > 0) {
                    countdownText = `${days} hari ${hours} jam ${minutes} menit`;
                } else if (hours > 0) {
                    countdownText = `${hours} jam ${minutes} menit ${seconds} detik`;
                } else if (minutes > 0) {
                    countdownText = `${minutes} menit ${seconds} detik`;
                } else {
                    countdownText = `${seconds} detik`;
                }
                
                countdownEl.textContent = countdownText;
            }, 1000);
            
            // Event listener untuk tombol download semua
            downloadAllBtn.addEventListener('click', function() {
                const fileLinks = document.querySelectorAll('#fileList a');
                fileLinks.forEach(link => {
                    link.click();
                });
            });
        });
        
        <?php
        // Fungsi helper untuk mendapatkan ikon file
        function getFileIcon($fileType) {
            if (strpos($fileType, 'image/') !== false) return 'fas fa-image';
            if (strpos($fileType, 'video/') !== false) return 'fas fa-video';
            if (strpos($fileType, 'pdf') !== false) return 'fas fa-file-pdf';
            if (strpos($fileType, 'word') !== false || strpos($fileType, 'document') !== false) return 'fas fa-file-word';
            if (strpos($fileType, 'excel') !== false || strpos($fileType, 'spreadsheet') !== false) return 'fas fa-file-excel';
            if (strpos($fileType, 'powerpoint') !== false || strpos($fileType, 'presentation') !== false) return 'fas fa-file-powerpoint';
            if (strpos($fileType, 'zip') !== false || strpos($fileType, 'rar') !== false || strpos($fileType, 'compressed') !== false) return 'fas fa-file-archive';
            return 'fas fa-file';
        }
        
        // Fungsi helper untuk mendapatkan nama file asli
        function getOriginalFileName($fileName) {
            // Format nama file: timestamp_nama_asli
            $parts = explode('_', $fileName, 2);
            if (count($parts) >= 2) {
                return $parts[1];
            }
            return $fileName;
        }
        
        // Fungsi helper untuk format ukuran file
        function formatFileSize($bytes) {
            if ($bytes === 0) return '0 Bytes';
            $k = 1024;
            $sizes = ['Bytes', 'KB', 'MB', 'GB'];
            $i = floor(log($bytes) / log($k));
            return floatval(number_format($bytes / pow($k, $i), 2)) . ' ' . $sizes[$i];
        }
        ?>
    </script>
</body>
</html>