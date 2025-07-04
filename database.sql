CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  nama varchar(100) NOT NULL,
  email varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  role enum('mahasiswa','asisten') NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel praktikum
CREATE TABLE praktikum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel modul
CREATE TABLE modul (
    id INT AUTO_INCREMENT PRIMARY KEY,
    praktikum_id INT NOT NULL,
    judul VARCHAR(100) NOT NULL,
    file_materi VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (praktikum_id) REFERENCES praktikum(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel pendaftaran
CREATE TABLE pendaftaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    praktikum_id INT NOT NULL,
    tanggal_daftar DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (praktikum_id) REFERENCES praktikum(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel laporan
CREATE TABLE laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pendaftaran_id INT NOT NULL,
    modul_id INT NOT NULL,
    file_laporan VARCHAR(255),
    status ENUM('pending','disetujui','ditolak') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran(id) ON DELETE CASCADE,
    FOREIGN KEY (modul_id) REFERENCES modul(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel nilai
CREATE TABLE nilai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laporan_id INT NOT NULL,
    nilai INT CHECK (nilai >= 0 AND nilai <= 100),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (laporan_id) REFERENCES laporan(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;