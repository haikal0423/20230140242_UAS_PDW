<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$praktikum_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cek apakah mahasiswa memang terdaftar di praktikum ini
$check = $conn->prepare("SELECT id FROM pendaftaran WHERE user_id = ? AND praktikum_id = ?");
$check->bind_param("ii", $user_id, $praktikum_id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    die("Kamu tidak terdaftar di praktikum ini.");
}
$check->close();

// Ambil data praktikum
$praktikum = $conn->query("SELECT * FROM praktikum WHERE id = $praktikum_id")->fetch_assoc();

// Ambil data modul
$modulList = [];
$sql = "SELECT m.*, 
        l.id AS laporan_id, l.file_laporan, l.status,
        n.nilai, n.feedback
        FROM modul m
        LEFT JOIN laporan l ON m.id = l.modul_id 
            AND l.pendaftaran_id = (SELECT id FROM pendaftaran WHERE user_id = $user_id AND praktikum_id = $praktikum_id LIMIT 1)
        LEFT JOIN nilai n ON l.id = n.laporan_id
        WHERE m.praktikum_id = $praktikum_id
        ORDER BY m.id";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $modulList[] = $row;
}

$pageTitle = "Detail Praktikum";
$activePage = "";
require_once 'templates/header_mahasiswa.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($praktikum['nama']); ?></h2>
    <p class="mb-6 text-gray-700"><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'])); ?></p>

    <h3 class="text-xl font-semibold mb-3">Daftar Modul & Tugas</h3>

    <?php if (empty($modulList)): ?>
        <p class="text-gray-600">Belum ada modul tersedia.</p>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($modulList as $modul): ?>
                <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                    <h4 class="text-lg font-bold text-blue-600"><?php echo htmlspecialchars($modul['judul']); ?></h4>

                    <?php if ($modul['file_materi']): ?>
                        <p class="mt-2">
                            📥 <a class="text-blue-500 underline" href="../uploads/<?php echo $modul['file_materi']; ?>" download>Unduh Materi</a>
                        </p>
                    <?php endif; ?>

                    <?php if ($modul['file_laporan']): ?>
                        <p class="mt-2">📤 Laporan kamu: 
                            <a href="../uploads/<?php echo $modul['file_laporan']; ?>" class="text-green-600 underline" target="_blank">Lihat File</a>
                        </p>
                        <p class="mt-1 text-sm text-gray-600">Status: <strong><?php echo $modul['status']; ?></strong></p>
                    <?php else: ?>
                        <form action="upload_laporan.php" method="POST" enctype="multipart/form-data" class="mt-3">
                            <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                            <input type="hidden" name="praktikum_id" value="<?php echo $praktikum_id; ?>">
                            <input type="file" name="laporan_file" required class="mb-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-4 rounded">Upload Laporan</button>
                        </form>
                    <?php endif; ?>

                    <?php if (!is_null($modul['nilai'])): ?>
                        <div class="mt-3 text-sm bg-green-100 p-3 rounded">
                            <p>✅ Nilai: <strong><?php echo $modul['nilai']; ?></strong></p>
                            <p>💬 Feedback: <?php echo nl2br(htmlspecialchars($modul['feedback'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
