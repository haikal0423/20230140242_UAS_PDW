<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Laporan Masuk";
$activePage = "laporan";

// Ambil data filter (jika ada)
$filter_status = $_GET['status'] ?? '';
$filter_query = '';
$params = [];

if ($filter_status && in_array($filter_status, ['pending', 'disetujui', 'ditolak'])) {
    $filter_query = "WHERE l.status = ?";
    $params[] = $filter_status;
}

// Query laporan + data terkait
$sql = "SELECT l.*, u.nama AS mahasiswa, m.judul AS modul, p.nama AS praktikum
        FROM laporan l
        JOIN pendaftaran pd ON l.pendaftaran_id = pd.id
        JOIN users u ON pd.user_id = u.id
        JOIN modul m ON l.modul_id = m.id
        JOIN praktikum p ON m.praktikum_id = p.id
        $filter_query
        ORDER BY l.created_at DESC";

$stmt = $conn->prepare($sql);

if ($filter_query) {
    $stmt->bind_param("s", ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$laporanList = $result->fetch_all(MYSQLI_ASSOC);

require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Laporan Masuk</h2>

    <!-- Filter -->
    <form method="GET" class="mb-4 flex gap-4">
        <select name="status" class="px-3 py-2 border rounded">
            <option value="">Semua Status</option>
            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="disetujui" <?php echo $filter_status === 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
            <option value="ditolak" <?php echo $filter_status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
        </select>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Filter</button>
    </form>

    <?php if (empty($laporanList)): ?>
        <p class="text-gray-600">Belum ada laporan ditemukan.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm text-gray-600">
                        <th class="px-4 py-2 border">Mahasiswa</th>
                        <th class="px-4 py-2 border">Praktikum</th>
                        <th class="px-4 py-2 border">Modul</th>
                        <th class="px-4 py-2 border">File</th>
                        <th class="px-4 py-2 border">Status</th>
                        <th class="px-4 py-2 border">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporanList as $lap): ?>
                        <tr class="text-sm">
                            <td class="px-4 py-2 border"><?php echo htmlspecialchars($lap['mahasiswa']); ?></td>
                            <td class="px-4 py-2 border"><?php echo htmlspecialchars($lap['praktikum']); ?></td>
                            <td class="px-4 py-2 border"><?php echo htmlspecialchars($lap['modul']); ?></td>
                            <td class="px-4 py-2 border">
                                <a href="../uploads/<?php echo $lap['file_laporan']; ?>" target="_blank" class="text-blue-600 underline">Lihat</a>
                            </td>
                            <td class="px-4 py-2 border">
                                <span class="px-2 py-1 rounded text-white 
                                    <?php 
                                        echo $lap['status'] === 'disetujui' ? 'bg-green-500' : 
                                            ($lap['status'] === 'ditolak' ? 'bg-red-500' : 'bg-yellow-500');
                                    ?>">
                                    <?php echo $lap['status']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 border">
                                <a href="nilai.php?id=<?php echo $lap['id']; ?>" class="bg-indigo-500 hover:bg-indigo-600 text-white py-1 px-3 rounded text-sm">Nilai</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>
