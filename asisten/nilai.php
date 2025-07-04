<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$laporan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data laporan & relasi
$sql = "SELECT l.*, u.nama AS mahasiswa, p.nama AS praktikum, m.judul AS modul
        FROM laporan l
        JOIN pendaftaran pd ON l.pendaftaran_id = pd.id
        JOIN users u ON pd.user_id = u.id
        JOIN modul m ON l.modul_id = m.id
        JOIN praktikum p ON m.praktikum_id = p.id
        WHERE l.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $laporan_id);
$stmt->execute();
$result = $stmt->get_result();
$laporan = $result->fetch_assoc();

if (!$laporan) {
    die("Laporan tidak ditemukan.");
}

// Handle form penilaian
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai = intval($_POST['nilai']);
    $feedback = trim($_POST['feedback']);
    $status = $_POST['status'];

    // Validasi nilai
    if ($nilai < 0 || $nilai > 100 || !in_array($status, ['disetujui', 'ditolak'])) {
        $error = "Data nilai tidak valid.";
    } else {
        // Update nilai (insert if not exist)
        $check = $conn->prepare("SELECT id FROM nilai WHERE laporan_id = ?");
        $check->bind_param("i", $laporan_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // Update
            $update = $conn->prepare("UPDATE nilai SET nilai = ?, feedback = ? WHERE laporan_id = ?");
            $update->bind_param("isi", $nilai, $feedback, $laporan_id);
            $update->execute();
        } else {
            // Insert
            $insert = $conn->prepare("INSERT INTO nilai (laporan_id, nilai, feedback) VALUES (?, ?, ?)");
            $insert->bind_param("iis", $laporan_id, $nilai, $feedback);
            $insert->execute();
        }
        $check->close();

        // Update status laporan
        $updateStatus = $conn->prepare("UPDATE laporan SET status = ? WHERE id = ?");
        $updateStatus->bind_param("si", $status, $laporan_id);
        $updateStatus->execute();

        // Redirect ke halaman laporan
        header("Location: laporan.php");
        exit();
    }
}

$pageTitle = "Nilai Laporan";
$activePage = "laporan";
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Nilai Laporan Mahasiswa</h2>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="mb-6">
        <p><strong>Nama Mahasiswa:</strong> <?php echo htmlspecialchars($laporan['mahasiswa']); ?></p>
        <p><strong>Praktikum:</strong> <?php echo htmlspecialchars($laporan['praktikum']); ?></p>
        <p><strong>Modul:</strong> <?php echo htmlspecialchars($laporan['modul']); ?></p>
        <p><strong>File Laporan:</strong> 
            <a href="../uploads/<?php echo $laporan['file_laporan']; ?>" target="_blank" class="text-blue-600 underline">Lihat File</a>
        </p>
    </div>

    <form method="POST">
        <div class="mb-4">
            <label for="nilai" class="block font-semibold mb-1">Nilai (0–100)</label>
            <input type="number" name="nilai" id="nilai" min="0" max="100" value="<?php echo isset($laporan['nilai']) ? $laporan['nilai'] : ''; ?>" required
                class="w-full px-3 py-2 border rounded">
        </div>

        <div class="mb-4">
            <label for="feedback" class="block font-semibold mb-1">Feedback</label>
            <textarea name="feedback" id="feedback" rows="4" class="w-full px-3 py-2 border rounded"><?php echo $laporan['feedback'] ?? ''; ?></textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Status Laporan</label>
            <select name="status" class="w-full px-3 py-2 border rounded" required>
                <option value="disetujui" <?php echo $laporan['status'] === 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                <option value="ditolak" <?php echo $laporan['status'] === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
            </select>
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">
            Simpan Penilaian
        </button>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
