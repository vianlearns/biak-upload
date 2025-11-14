<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Berkas - Biro Akademik UDINUS</title>
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📁</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Upload Berkas</h1>
            <p class="text-gray-600">Biro Akademik UDINUS - Upload file tanpa kompresi</p>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
            <form id="uploadForm" class="space-y-6">
                <!-- Drag and Drop Area -->
                <div class="drag-area rounded-lg p-8 text-center cursor-pointer hover:bg-gray-50" id="dragArea">
                    <div class="space-y-4">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="text-gray-600">
                            <p class="text-lg font-medium">Klik untuk memilih file atau drag & drop</p>
                            <p class="text-sm text-gray-500 mt-1">Mendukung semua jenis file (maksimal 500MB)</p>
                        </div>
                    </div>
                    <input type="file" id="fileInput" multiple class="hidden" accept="*/*">
                </div>

                <!-- Selected Files -->
                <div id="fileList" class="space-y-2 hidden">
                    <h3 class="text-lg font-semibold text-gray-800">File yang dipilih:</h3>
                    <div id="fileItems" class="space-y-2"></div>
                </div>

                <!-- Auto Delete Options -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">File akan dihapus otomatis dalam:</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <label class="relative">
                            <input type="radio" name="deleteTime" value="15" class="sr-only peer" checked>
                            <div class="p-3 bg-white border border-gray-300 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                <div class="text-center">
                                    <div class="font-semibold text-gray-900">15 Menit</div>
                                </div>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="deleteTime" value="30" class="sr-only peer">
                            <div class="p-3 bg-white border border-gray-300 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                <div class="text-center">
                                    <div class="font-semibold text-gray-900">30 Menit</div>
                                </div>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="deleteTime" value="60" class="sr-only peer">
                            <div class="p-3 bg-white border border-gray-300 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                <div class="text-center">
                                    <div class="font-semibold text-gray-900">1 Jam</div>
                                </div>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="deleteTime" value="180" class="sr-only peer">
                            <div class="p-3 bg-white border border-gray-300 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                <div class="text-center">
                                    <div class="font-semibold text-gray-900">3 Jam</div>
                                </div>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="deleteTime" value="360" class="sr-only peer">
                            <div class="p-3 bg-white border border-gray-300 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                <div class="text-center">
                                    <div class="font-semibold text-gray-900">6 Jam</div>
                                </div>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="deleteTime" value="1440" class="sr-only peer">
                            <div class="p-3 bg-white border border-gray-300 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                <div class="text-center">
                                    <div class="font-semibold text-gray-900">1 Hari</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Upload Button -->
                <button type="submit" id="uploadBtn" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="uploadBtnText">Upload File</span>
                </button>
            </form>

            <!-- Progress Area -->
            <div id="progressArea" class="mt-6 space-y-4 hidden">
                <h3 class="text-lg font-semibold text-gray-800">Proses Upload:</h3>
                <div id="progressBars" class="space-y-3"></div>
            </div>

            <!-- Result Area -->
            <div id="resultArea" class="mt-6 hidden">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Upload Berhasil!</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>Link untuk mengakses file Anda:</p>
                                <div class="mt-2 p-2 bg-white rounded border">
                                    <code id="resultLink" class="text-blue-600 font-mono"></code>
                                </div>
                                <p class="mt-2 text-xs">Bagikan link ini kepada staf Biro Akademik</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>&copy; 2024 Biro Akademik UDINUS. Website ini untuk keperluan sementara.</p>
        </div>
    </div>

    <script>
        // Drag and drop functionality
        const dragArea = document.getElementById('dragArea');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const fileItems = document.getElementById('fileItems');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.getElementById('uploadBtn');
        const uploadBtnText = document.getElementById('uploadBtnText');
        const progressArea = document.getElementById('progressArea');
        const progressBars = document.getElementById('progressBars');
        const resultArea = document.getElementById('resultArea');
        const resultLink = document.getElementById('resultLink');

        let selectedFiles = [];

        // Drag and drop events
        dragArea.addEventListener('click', () => fileInput.click());
        
        dragArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dragArea.classList.add('active');
        });

        dragArea.addEventListener('dragleave', () => {
            dragArea.classList.remove('active');
        });

        dragArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dragArea.classList.remove('active');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            const maxSize = 500 * 1024 * 1024; // 500MB dalam bytes
            const validFiles = [];
            
            Array.from(files).forEach(file => {
                if (file.size > maxSize) {
                    alert(`File "${file.name}" terlalu besar (${formatFileSize(file.size)}). Maksimal ukuran file adalah 500MB.`);
                } else {
                    validFiles.push(file);
                }
            });
            
            selectedFiles = validFiles;
            displayFiles();
        }

        function displayFiles() {
            if (selectedFiles.length === 0) {
                fileList.classList.add('hidden');
                return;
            }

            fileList.classList.remove('hidden');
            fileItems.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                fileItem.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                            <p class="text-sm text-gray-500">${formatFileSize(file.size)}</p>
                        </div>
                    </div>
                    <button type="button" onclick="removeFile(${index})" class="text-red-600 hover:text-red-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
                fileItems.appendChild(fileItem);
            });
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            displayFiles();
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Form submission
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (selectedFiles.length === 0) {
                alert('Silakan pilih file terlebih dahulu');
                return;
            }

            const deleteTime = document.querySelector('input[name="deleteTime"]:checked').value;
            
            uploadBtn.disabled = true;
            uploadBtnText.textContent = 'Mengupload...';
            progressArea.classList.remove('hidden');
            resultArea.classList.add('hidden');
            progressBars.innerHTML = '';

            // Calculate total size
            const totalSize = selectedFiles.reduce((sum, file) => sum + file.size, 0);
            const maxTotalSize = 500 * 1024 * 1024; // 500MB total
            
            if (totalSize > maxTotalSize) {
                alert(`Total ukuran file (${formatFileSize(totalSize)}) melebihi batas 500MB.`);
                uploadBtn.disabled = false;
                uploadBtnText.textContent = 'Upload File';
                progressArea.classList.add('hidden');
                return;
            }

            // Create progress bars for each file
            selectedFiles.forEach((file, index) => {
                const progressItem = document.createElement('div');
                progressItem.className = 'bg-gray-100 rounded-lg p-4';
                progressItem.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 truncate">${file.name}</span>
                        <span class="text-sm text-gray-500">${formatFileSize(file.size)}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="progress-bar bg-blue-600 h-2 rounded-full" style="width: 0%" id="progress-${index}"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1" id="progress-text-${index}">0%</div>
                `;
                progressBars.appendChild(progressItem);
            });

            const formData = new FormData();
            selectedFiles.forEach((file, index) => {
                formData.append('files[]', file);
            });
            formData.append('deleteTime', deleteTime);

            try {
                const response = await fetch('upload.php', {
                    method: 'POST',
                    body: formData
                });

                // Check if response is OK
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Get response text first to check if it's valid JSON
                const responseText = await response.text();
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Invalid JSON response:', responseText);
                    throw new Error('Invalid server response. Please try again.');
                }

                if (result.status === 'success') {
                    // Update all progress bars to 100%
                    selectedFiles.forEach((file, index) => {
                        const progressBar = document.getElementById(`progress-${index}`);
                        const progressText = document.getElementById(`progress-text-${index}`);
                        if (progressBar) {
                            progressBar.style.width = '100%';
                            progressText.textContent = '100% - Selesai';
                        }
                    });
                    
                    showUploadResult(result);
                    selectedFiles = [];
                    displayFiles();
                    uploadForm.reset();
                } else {
                    throw new Error(result.message || 'Upload gagal');
                }
            } catch (error) {
                alert('Error: ' + error.message);
                console.error('Upload error:', error);
            } finally {
                uploadBtn.disabled = false;
                uploadBtnText.textContent = 'Upload File';
            }
        });

        function showUploadResult(result) {
            progressArea.classList.add('hidden');
            resultArea.classList.remove('hidden');
            resultLink.textContent = `${window.location.origin}/${result.id}`;
            resultArea.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>