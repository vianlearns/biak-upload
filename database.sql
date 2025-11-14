-- Database: biro_akademik_upload
-- Tabel untuk menyimpan data upload file

CREATE DATABASE IF NOT EXISTS biak_upload;
USE biak_upload;

-- Tabel uploads untuk menyimpan metadata file
CREATE TABLE IF NOT EXISTS uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_urutan INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    nama_asli VARCHAR(255) NOT NULL,
    jenis_file VARCHAR(100) NOT NULL,
    ukuran_file BIGINT NOT NULL,
    waktu_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    waktu_hapus DATETIME NOT NULL,
    ip_address VARCHAR(45),
    INDEX idx_id_urutan (id_urutan),
    INDEX idx_waktu_hapus (waktu_hapus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk menyimpan counter ID urutan
CREATE TABLE IF NOT EXISTS counter (
    id INT PRIMARY KEY DEFAULT 1,
    last_id_urutan INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial counter
INSERT INTO counter (id, last_id_urutan) VALUES (1, 0) ON DUPLICATE KEY UPDATE id = 1;