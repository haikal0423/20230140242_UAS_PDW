<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Validasi input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modul_id'], $_FILES['laporan_file'])) {
    $modul_id = intval($_POST['modul_id']);
    $praktikum_id = intval($_POST['praktikum_id']);

    // Ambil ID pendaftaran mahasiswa untuk praktikum ini
    $stmt = $conn->prepare("SELECT id FROM pendaftaran WHERE user_id = ? AND praktikum_id = ?");
    $stmt->bind_param("ii", $user_id, $praktikum_id);
    $stmt->execute();
    $stmt->bind_result($pendaftaran_id);
    $stmt->fetch();
    $stmt->close();

    if (!$pendaftaran_id) {
        die("Pendaftaran tidak ditemukan.");
    }

    // Cek apakah sudah pernah upload
    $check = $conn->prepare("SELECT id FROM laporan WHERE pendaftaran_id = ? AND modul_id = ?");
    $check->bind_param("ii", $pendaftaran_id, $modul_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        die("Kamu sudah mengumpulkan laporan untuk modul ini.");
    }
    $check->close();

    // Upload file
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $original_name = basename($_FILES["laporan_file"]["name"]);
    $ext = pathinfo($original_name, PATHINFO_EXTENSION);
    $safe_name = uniqid("laporan_") . "." . $ext;
    $target_file = $target_dir . $safe_name;

    if (move_uploaded_file($_FILES["laporan_file"]["tmp_name"], $target_file)) {
        // Simpan ke database
        $insert = $conn->prepare("INSERT INTO laporan (pendaftaran_id, modul_id, file_laporan) VALUES (?, ?, ?)");
        $insert->bind_param("iis", $pendaftaran_id, $modul_id, $safe_name);
        if ($insert->execute()) {
            header("Location: course_detail.php?id=$praktikum_id");
            exit();
        } else {
            echo "Gagal menyimpan ke database.";
        }
        $insert->close();
    } else {
        echo "Gagal mengunggah file.";
    }
} else {
    echo "Permintaan tidak valid.";
}
?>
