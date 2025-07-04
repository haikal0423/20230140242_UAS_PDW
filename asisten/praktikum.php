<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Manajemen Praktikum";
$activePage = "praktikum";

// Handle tambah praktikum
if (isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);

    if (!empty($nama)) {
        $stmt = $conn->prepare("INSERT INTO praktikum (nama, deskripsi) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama, $deskripsi);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: praktikum.php");
    exit();
}

// Handle edit praktikum
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);

    if (!empty($nama)) {
        $stmt = $conn->prepare("UPDATE praktikum SET nama = ?, deskripsi = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama, $deskripsi, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: praktikum.php");
    exit();
}

// Handle hapus praktikum
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM praktikum WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: praktikum.php");
    exit();
}

// Ambil semua praktikum
$result = $conn->query("SELECT * FROM praktikum ORDER BY id DESC");
$praktikumList = $result->fetch_all(MYSQLI_ASSOC);

require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Manajemen Mata Praktikum</h2>

    <!-- Form Tambah -->
    <form method="POST" class="mb-6 space-y-3">
        <input type="hidden" name="action" value="tambah">
        <div>
            <label class="block font-semibold">Nama Praktikum</label>
            <input type="text" name="nama" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div>
            <label class="block font-semibold">Deskripsi</label>
            <textarea name="deskripsi" class="w-full px-3 py-2 border rounded" rows="3"></textarea>
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
            Tambah Praktikum
        </button>
    </form>

    <!-- Tabel Daftar Praktikum -->
    <?php if (empty($praktikumList)): ?>
        <p class="text-gray-600">Belum ada data praktikum.</p>
    <?php else: ?>
        <table class="w-full text-sm border">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-2 border">Nama</th>
                    <th class="p-2 border">Deskripsi</th>
                    <th class="p-2 border w-32">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($praktikumList as $praktikum): ?>
                    <tr>
                        <td class="p-2 border"><?php echo htmlspecialchars($praktikum['nama']); ?></td>
                        <td class="p-2 border"><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'])); ?></td>
                        <td class="p-2 border">
                            <!-- Form Edit (inline) -->
                            <button onclick="toggleEdit('<?php echo $praktikum['id']; ?>')" class="text-blue-600 text-sm">Edit</button>
                            <a href="?hapus=<?php echo $praktikum['id']; ?>" onclick="return confirm('Yakin hapus?')" class="text-red-600 text-sm ml-2">Hapus</a>
                        </td>
                    </tr>
                    <tr id="edit-<?php echo $praktikum['id']; ?>" style="display:none;">
                        <td colspan="3" class="p-2 border bg-gray-50">
                            <form method="POST" class="space-y-2">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $praktikum['id']; ?>">
                                <input type="text" name="nama" class="w-full px-3 py-1 border rounded" value="<?php echo htmlspecialchars($praktikum['nama']); ?>" required>
                                <textarea name="deskripsi" class="w-full px-3 py-1 border rounded" rows="2"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></textarea>
                                <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded text-sm">Simpan</button>
                                <button type="button" onclick="toggleEdit('<?php echo $praktikum['id']; ?>')" class="text-sm text-gray-500 ml-2">Batal</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function toggleEdit(id) {
    const row = document.getElementById("edit-" + id);
    row.style.display = row.style.display === "none" ? "table-row" : "none";
}
</script>

<?php require_once 'templates/footer.php'; ?>
