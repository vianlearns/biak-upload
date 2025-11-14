<?php
require_once 'config.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ID tidak valid");
}

// Ambil data file dari database
$stmt = $pdo->prepare("SELECT * FROM uploads WHERE id_urutan = ? ORDER BY waktu_upload ASC");
$stmt->execute([$id]);
$files = $stmt->fetchAll();

if (empty($files)) {
    die("Tidak ada file dengan ID tersebut");
}

// Hitung waktu tersisa
$firstFile = $files[0];
$deleteTime = strtotime($firstFile['waktu_hapus']);
$now = time();
$timeRemaining = $deleteTime - $now;

// Format waktu tersisa
function formatTimeRemaining($seconds) {
    if ($seconds <= 0) {
        return "File telah kadaluarsa";
    }
    
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    $parts = [];
    if ($days > 0) $parts[] = "$days hari";
    if ($hours > 0) $parts[] = "$hours jam";
    if ($minutes > 0) $parts[] = "$minutes menit";
    
    return implode(' ', $parts) . " lagi";
}

$timeRemainingText = formatTimeRemaining($timeRemaining);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload #<?= $id ?> - Biro Akademik UDINUS</title>
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📁</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
        .file-item {
            transition: all 0.3s ease;
        }
        .file-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .download-btn {
            transition: all 0.2s ease;
        }
        .download-btn:hover {
            transform: scale(1.05);
        }
    </style>
    <style>
        .animate-fade-in {
            animation: fade-in 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Biro Akademik UDINUS</h1>
            <p class="text-gray-600">File Upload #<?= $id ?></p>
        </div>

        <!-- Info Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600"><?= count($files) ?></div>
                    <div class="text-sm text-gray-600">Total File</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">
                        <?= number_format(array_sum(array_column($files, 'ukuran_file')) / 1024 / 1024, 2) ?> MB
                    </div>
                    <div class="text-sm text-gray-600">Total Ukuran</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold <?= $timeRemaining <= 0 ? 'text-red-600' : 'text-orange-600' ?>">
                        <?= $timeRemainingText ?>
                    </div>
                    <div class="text-sm text-gray-600">Waktu Tersisa</div>
                </div>
            </div>
        </div>

        <!-- Warning if expired -->
        <?php if ($timeRemaining <= 0): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">File Kadaluarsa</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>File ini telah melebihi batas waktu dan mungkin sudah dihapus otomatis oleh sistem.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Download All Button -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Daftar File</h2>
                    <p class="text-sm text-gray-600">Upload: <?= date('d/m/Y H:i', strtotime($firstFile['waktu_upload'])) ?></p>
                </div>
                <button onclick="downloadAll()" class="download-btn bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Semua
                </button>
            </div>
        </div>

        <!-- Files List -->
        <div class="space-y-4">
            <?php foreach ($files as $index => $file): ?>
            <div class="file-item bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 flex-1 min-w-0">
                        <!-- File Icon -->
                        <div class="flex-shrink-0">
                            <?php
                            $extension = strtolower(pathinfo($file['nama_asli'], PATHINFO_EXTENSION));
                            $iconClass = 'text-gray-400';
                            
                            if (in_array($extension, ['pdf'])) $iconClass = 'text-red-500';
                            elseif (in_array($extension, ['doc', 'docx'])) $iconClass = 'text-blue-500';
                            elseif (in_array($extension, ['xls', 'xlsx'])) $iconClass = 'text-green-500';
                            elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) $iconClass = 'text-purple-500';
                            elseif (in_array($extension, ['zip', 'rar'])) $iconClass = 'text-yellow-500';
                            elseif (in_array($extension, ['mp4', 'avi', 'mkv'])) $iconClass = 'text-indigo-500';
                            ?>
                            <svg class="h-10 w-10 <?= $iconClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        
                        <!-- File Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($file['nama_asli']) ?></h3>
                            <p class="text-sm text-gray-500">
                                <?= number_format($file['ukuran_file'] / 1024 / 1024, 2) ?> MB • 
                                <?= date('d/m/Y H:i', strtotime($file['waktu_upload'])) ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Download Button -->
                    <a href="download.php?id=<?= $file['id'] ?>" 
                       class="download-btn inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>&copy; 2024 Biro Akademik UDINUS. Website ini untuk keperluan sementara.</p>
        </div>
    </div>

    <script>
        function downloadAll() {
            const fileIds = <?= json_encode(array_column($files, 'id')) ?>;
            
            // Download file satu per satu dengan delay kecil
            fileIds.forEach((id, index) => {
                setTimeout(() => {
                    const link = document.createElement('a');
                    link.href = `download.php?id=${id}`;
                    link.download = '';
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }, index * 500); // Delay 500ms antar file
            });
        }

        // Auto refresh setiap 30 detik untuk update waktu tersisa
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>