<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Manajemen Modul";
$activePage = "modul";

// Handle tambah modul
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'tambah') {
    $praktikum_id = intval($_POST['praktikum_id']);
    $judul = trim($_POST['judul']);

    $file_materi = null;
    if (!empty($_FILES['file_materi']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $ext = pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION);
        $file_materi = uniqid("materi_") . "." . $ext;
        move_uploaded_file($_FILES['file_materi']['tmp_name'], $target_dir . $file_materi);
    }

    $stmt = $conn->prepare("INSERT INTO modul (praktikum_id, judul, file_materi) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $praktikum_id, $judul, $file_materi);
    $stmt->execute();
    $stmt->close();

    header("Location: modul.php");
    exit();
}

// Handle hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM modul WHERE id = $id");
    header("Location: modul.php");
    exit();
}

// Ambil semua praktikum
$praktikumResult = $conn->query("SELECT * FROM praktikum");
$praktikumList = $praktikumResult->fetch_all(MYSQLI_ASSOC);

// Ambil semua modul
$sql = "SELECT m.*, p.nama AS praktikum FROM modul m JOIN praktikum p ON m.praktikum_id = p.id ORDER BY m.created_at DESC";
$modulResult = $conn->query($sql);
$modulList = $modulResult->fetch_all(MYSQLI_ASSOC);

require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Manajemen Modul Praktikum</h2>

    <!-- Form Tambah Modul -->
    <form method="POST" enctype="multipart/form-data" class="space-y-4 mb-6">
        <input type="hidden" name="action" value="tambah">
        <div>
            <label class="block font-semibold">Praktikum</label>
            <select name="praktikum_id" required class="w-full px-3 py-2 border rounded">
                <option value="">-- Pilih Praktikum --</option>
                <?php foreach ($praktikumList as $praktikum): ?>
                    <option value="<?php echo $praktikum['id']; ?>"><?php echo htmlspecialchars($praktikum['nama']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block font-semibold">Judul Modul</label>
            <input type="text" name="judul" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div>
            <label class="block font-semibold">File Materi (PDF/DOCX)</label>
            <input type="file" name="file_materi" class="w-full" accept=".pdf,.doc,.docx" required>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Tambah Modul</button>
    </form>

    <!-- Daftar Modul -->
    <?php if (empty($modulList)): ?>
        <p class="text-gray-600">Belum ada modul yang ditambahkan.</p>
    <?php else: ?>
        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">Praktikum</th>
                    <th class="p-2 border">Judul</th>
                    <th class="p-2 border">Materi</th>
                    <th class="p-2 border w-32">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modulList as $modul): ?>
                    <tr>
                        <td class="p-2 border"><?php echo htmlspecialchars($modul['praktikum']); ?></td>
                        <td class="p-2 border"><?php echo htmlspecialchars($modul['judul']); ?></td>
                        <td class="p-2 border">
                            <?php if ($modul['file_materi']): ?>
                                <a href="../uploads/<?php echo $modul['file_materi']; ?>" class="text-blue-600 underline" target="_blank">Unduh</a>
                            <?php else: ?>
                                <span class="text-gray-400 italic">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-2 border">
                            <a href="?hapus=<?php echo $modul['id']; ?>" onclick="return confirm('Hapus modul ini?')" class="text-red-600 text-sm">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>
