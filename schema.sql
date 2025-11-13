CREATE TABLE `uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_urutan` int(11) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `jenis_berkas` varchar(100) NOT NULL,
  `ukuran_file` bigint(20) NOT NULL,
  `waktu_upload` datetime NOT NULL,
  `waktu_hapus` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_urutan` (`id_urutan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;