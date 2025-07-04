<?php
session_start();
require_once '../config.php';

// Pastikan hanya mahasiswa yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data praktikum yang sudah didaftarkan user
$sql = "SELECT p.id, p.nama, p.deskripsi 
        FROM pendaftaran dp
        JOIN praktikum p ON dp.praktikum_id = p.id
        WHERE dp.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$praktikumList = $result->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Praktikum Saya";
$activePage = "my_courses";
require_once 'templates/header_mahasiswa.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Praktikum yang Kamu Ikuti</h2>

    <?php if (empty($praktikumList)): ?>
        <p class="text-gray-600">Kamu belum mendaftar ke praktikum manapun.</p>
    <?php else: ?>
        <div class="grid md:grid-cols-2 gap-6">
            <?php foreach ($praktikumList as $praktikum): ?>
                <div class="p-4 bg-gray-100 rounded-lg shadow">
                    <h3 class="text-lg font-bold text-blue-600"><?php echo htmlspecialchars($praktikum['nama']); ?></h3>
                    <p class="mt-2 text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'])); ?></p>
                    <a href="course_detail.php?id=<?php echo $praktikum['id']; ?>" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                        Lihat Detail
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
