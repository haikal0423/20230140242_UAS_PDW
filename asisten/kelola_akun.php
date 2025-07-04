<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Kelola Akun Pengguna";
$activePage = "kelola_akun";

// Tambah akun
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'tambah') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    if (!empty($nama) && !empty($email) && !empty($password)) {
        $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $email, $password, $role);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: kelola_akun.php");
    exit();
}

// Edit akun
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edit') {
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nama, $email, $role, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: kelola_akun.php");
    exit();
}

// Hapus akun
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $id");
    }
    header("Location: kelola_akun.php");
    exit();
}

// Ambil data semua user
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
$userList = $result->fetch_all(MYSQLI_ASSOC);

require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Kelola Akun Pengguna</h2>

    <!-- Form Tambah -->
    <form method="POST" class="space-y-3 mb-6">
        <input type="hidden" name="action" value="tambah">
        <div>
            <label class="block font-semibold">Nama</label>
            <input type="text" name="nama" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div>
            <label class="block font-semibold">Email</label>
            <input type="email" name="email" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div>
            <label class="block font-semibold">Password</label>
            <input type="password" name="password" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div>
            <label class="block font-semibold">Role</label>
            <select name="role" class="w-full px-3 py-2 border rounded" required>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asisten">Asisten</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Tambah Akun</button>
    </form>

    <!-- Tabel Akun -->
    <table class="w-full text-sm border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border">Nama</th>
                <th class="p-2 border">Email</th>
                <th class="p-2 border">Role</th>
                <th class="p-2 border w-32">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($userList as $user): ?>
                <tr>
                    <td class="p-2 border"><?php echo htmlspecialchars($user['nama']); ?></td>
                    <td class="p-2 border"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="p-2 border"><?php echo $user['role']; ?></td>
                    <td class="p-2 border">
                        <button onclick="toggleEdit('<?php echo $user['id']; ?>')" class="text-blue-600 text-sm">Edit</button>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?hapus=<?php echo $user['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="text-red-600 text-sm ml-2">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr id="edit-<?php echo $user['id']; ?>" style="display:none;">
                    <td colspan="4" class="p-2 border bg-gray-50">
                        <form method="POST" class="space-y-2">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <input type="text" name="nama" class="w-full px-3 py-1 border rounded" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                            <input type="email" name="email" class="w-full px-3 py-1 border rounded" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <select name="role" class="w-full px-3 py-1 border rounded">
                                <option value="mahasiswa" <?php echo $user['role'] === 'mahasiswa' ? 'selected' : ''; ?>>Mahasiswa</option>
                                <option value="asisten" <?php echo $user['role'] === 'asisten' ? 'selected' : ''; ?>>Asisten</option>
                            </select>
                            <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded text-sm">Simpan</button>
                            <button type="button" onclick="toggleEdit('<?php echo $user['id']; ?>')" class="text-sm text-gray-500 ml-2">Batal</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function toggleEdit(id) {
    const row = document.getElementById("edit-" + id);
    row.style.display = row.style.display === "none" ? "table-row" : "none";
}
</script>

<?php require_once 'templates/footer.php'; ?>
