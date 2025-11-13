<?php
require_once 'config.php';

// Mendapatkan ID urutan berikutnya
 $result = $conn->query("SELECT MAX(id_urutan) as max_id FROM uploads");
 $row = $result->fetch_assoc();
 $next_id = $row['max_id'] + 1;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Berkas - Biro Akademik UDINUS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .drag-area {
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
        }
        .drag-area.active {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .progress-container {
            display: none;
        }
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
            <h1 class="text-3xl font-bold text-blue-800 mb-2">Upload Berkas Sementara</h1>
            <p class="text-gray-600">Biro Akademik UDINUS</p>
        </header>

        <main class="bg-white rounded-lg shadow-md p-6">
            <div id="upload-form" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File</label>
                    <div id="dropArea" class="drag-area rounded-lg p-8 text-center cursor-pointer">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600 mb-2">Seret dan lepas file di sini atau</p>
                        <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
                            Pilih File
                        </button>
                        <input type="file" id="fileInput" multiple class="hidden">
                        <p class="text-xs text-gray-500 mt-2">Semua jenis file diperbolehkan</p>
                    </div>
                </div>

                <div id="fileList" class="space-y-2"></div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Hapus Otomatis</label>
                    <select id="deleteTime" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="15">15 menit</option>
                        <option value="30">30 menit</option>
                        <option value="60">1 jam</option>
                        <option value="180">3 jam</option>
                        <option value="360">6 jam</option>
                        <option value="1440">1 hari</option>
                    </select>
                </div>

                <button id="uploadBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition">
                    <i class="fas fa-upload mr-2"></i>Upload File
                </button>
            </div>

            <div id="resultContainer" class="hidden">
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                    <h2 class="text-xl font-semibold text-green-800 mb-2">Upload Berhasil!</h2>
                    <p class="text-gray-700 mb-4">Biro Akademik dapat mengakses file Anda melalui link berikut:</p>
                    <div class="bg-white p-3 rounded border border-gray-200 mb-4">
                        <p class="font-mono text-lg" id="accessLink"></p>
                    </div>
                    <button id="copyBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
                        <i class="fas fa-copy mr-2"></i>Salin Link
                    </button>
                    <button id="newUploadBtn" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition">
                        <i class="fas fa-plus mr-2"></i>Upload Baru
                    </button>
                </div>
            </div>

            <div id="progressContainer" class="progress-container">
                <h3 class="text-lg font-medium mb-3">Mengunggah File...</h3>
                <div id="progressList" class="space-y-2"></div>
            </div>
        </main>

        <footer class="text-center mt-8 text-gray-600 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Biro Akademik UDINUS - Sistem Upload Sementara</p>
        </footer>
    </div>

    <div id="notification" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg transform translate-y-full transition-transform duration-300">
        <span id="notificationText"></span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropArea = document.getElementById('dropArea');
            const fileInput = document.getElementById('fileInput');
            const fileList = document.getElementById('fileList');
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadForm = document.getElementById('upload-form');
            const resultContainer = document.getElementById('resultContainer');
            const progressContainer = document.getElementById('progressContainer');
            const progressList = document.getElementById('progressList');
            const copyBtn = document.getElementById('copyBtn');
            const newUploadBtn = document.getElementById('newUploadBtn');
            const accessLink = document.getElementById('accessLink');
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');
            
            let selectedFiles = [];
            
            // Event listeners untuk drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => {
                    dropArea.classList.add('active');
                }, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => {
                    dropArea.classList.remove('active');
                }, false);
            });
            
            dropArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }
            
            dropArea.addEventListener('click', () => {
                fileInput.click();
            });
            
            fileInput.addEventListener('change', () => {
                handleFiles(fileInput.files);
            });
            
            function handleFiles(files) {
                ([...files]).forEach(file => {
                    if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                        selectedFiles.push(file);
                    }
                });
                updateFileList();
            }
            
            function updateFileList() {
                fileList.innerHTML = '';
                
                if (selectedFiles.length === 0) {
                    fileList.innerHTML = '<p class="text-gray-500 text-sm">Belum ada file yang dipilih</p>';
                    return;
                }
                
                selectedFiles.forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item flex items-center justify-between p-2 bg-gray-50 rounded';
                    
                    const fileInfo = document.createElement('div');
                    fileInfo.className = 'flex items-center';
                    
                    const fileIcon = getFileIcon(file.type);
                    fileInfo.innerHTML = `
                        <i class="${fileIcon} mr-2 text-gray-600"></i>
                        <div>
                            <p class="text-sm font-medium">${file.name}</p>
                            <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                        </div>
                    `;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'text-red-500 hover:text-red-700';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.addEventListener('click', () => {
                        selectedFiles.splice(index, 1);
                        updateFileList();
                    });
                    
                    fileItem.appendChild(fileInfo);
                    fileItem.appendChild(removeBtn);
                    fileList.appendChild(fileItem);
                });
            }
            
            function getFileIcon(fileType) {
                if (fileType.startsWith('image/')) return 'fas fa-image';
                if (fileType.startsWith('video/')) return 'fas fa-video';
                if (fileType.includes('pdf')) return 'fas fa-file-pdf';
                if (fileType.includes('word') || fileType.includes('document')) return 'fas fa-file-word';
                if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'fas fa-file-excel';
                if (fileType.includes('powerpoint') || fileType.includes('presentation')) return 'fas fa-file-powerpoint';
                if (fileType.includes('zip') || fileType.includes('rar') || fileType.includes('compressed')) return 'fas fa-file-archive';
                return 'fas fa-file';
            }
            
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            uploadBtn.addEventListener('click', uploadFiles);
            const BASE_URL = '<?php echo BASE_URL; ?>';
            
            function uploadFiles() {
                if (selectedFiles.length === 0) {
                    showNotification('Pilih setidaknya satu file untuk diunggah', 'error');
                    return;
                }
                
                const deleteTime = document.getElementById('deleteTime').value;
                const formData = new FormData();
                
                selectedFiles.forEach(file => {
                    formData.append('files[]', file);
                });
                
                formData.append('deleteTime', deleteTime);
                
                // Tampilkan progress container
                uploadForm.classList.add('hidden');
                progressContainer.classList.remove('hidden');
                progressList.innerHTML = '';
                
                // Buat progress bar untuk setiap file
                selectedFiles.forEach((file, index) => {
                    const progressItem = document.createElement('div');
                    progressItem.className = 'mb-2';
                    progressItem.innerHTML = `
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium">${file.name}</span>
                            <span class="text-sm font-medium progress-percent-${index}">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full progress-bar-${index}" style="width: 0%"></div>
                        </div>
                    `;
                    progressList.appendChild(progressItem);
                });
                
                fetch('upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const link = `${BASE_URL}view.php?id=${data.id}`;
                        accessLink.textContent = link;
                        
                        setTimeout(() => {
                            progressContainer.classList.add('hidden');
                            resultContainer.classList.remove('hidden');
                        }, 1000);
                    } else {
                        showNotification('Terjadi kesalahan: ' + data.message, 'error');
                        resetForm();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Terjadi kesalahan saat mengunggah file', 'error');
                    resetForm();
                });
            }
            
            copyBtn.addEventListener('click', () => {
                const link = accessLink.textContent;
                navigator.clipboard.writeText(link).then(() => {
                    showNotification('Link berhasil disalin!', 'success');
                }).catch(err => {
                    console.error('Gagal menyalin link: ', err);
                });
            });
            
            newUploadBtn.addEventListener('click', resetForm);
            
            function resetForm() {
                selectedFiles = [];
                updateFileList();
                uploadForm.classList.remove('hidden');
                resultContainer.classList.add('hidden');
                progressContainer.classList.add('hidden');
                fileInput.value = '';
            }
            
            function showNotification(message, type = 'success') {
                notificationText.textContent = message;
                
                if (type === 'error') {
                    notification.className = notification.className.replace('bg-green-500', 'bg-red-500');
                } else {
                    notification.className = notification.className.replace('bg-red-500', 'bg-green-500');
                }
                
                notification.style.transform = 'translateY(0)';
                
                setTimeout(() => {
                    notification.style.transform = 'translateY(100%)';
                }, 3000);
            }
            
            // Inisialisasi daftar file
            updateFileList();
        });
    </script>
</body>
</html>