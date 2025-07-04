<?php
session_start();
require_once '../config.php';

// Cek apakah user login
$isLoggedIn = isset($_SESSION['user_id']);
$isMahasiswa = $isLoggedIn && $_SESSION['role'] === 'mahasiswa';

// Jika mahasiswa menekan tombol "Daftar"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['praktikum_id'])) {
    $user_id = $_SESSION['user_id'];
    $praktikum_id = intval($_POST['praktikum_id']);

    // Cek apakah sudah mendaftar
    $check = $conn->prepare("SELECT id FROM pendaftaran WHERE user_id = ? AND praktikum_id = ?");
    $check->bind_param("ii", $user_id, $praktikum_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Kamu sudah terdaftar di praktikum ini.";
    } else {
        // Daftarkan mahasiswa
        $stmt = $conn->prepare("INSERT INTO pendaftaran (user_id, praktikum_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $praktikum_id);
        if ($stmt->execute()) {
            $message = "Berhasil mendaftar ke praktikum.";
        } else {
            $message = "Gagal mendaftar.";
        }
        $stmt->close();
    }
    $check->close();
}

// Ambil semua data praktikum
$result = $conn->query("SELECT * FROM praktikum");
$praktikumList = $result->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Katalog Praktikum";
$activePage = "courses";
require_once 'templates/header_mahasiswa.php';
?>

<?php if (isset($message)): ?>
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Daftar Mata Praktikum</h2>

    <?php if (empty($praktikumList)): ?>
        <p class="text-gray-600">Belum ada data praktikum.</p>
    <?php else: ?>
        <div class="grid md:grid-cols-2 gap-6">
            <?php foreach ($praktikumList as $praktikum): ?>
                <div class="p-4 bg-gray-100 rounded-lg shadow">
                    <h3 class="text-lg font-bold text-blue-600"><?php echo htmlspecialchars($praktikum['nama']); ?></h3>
                    <p class="mt-2 text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'])); ?></p>

                    <?php if ($isMahasiswa): ?>
                        <form method="POST" class="mt-4">
                            <input type="hidden" name="praktikum_id" value="<?php echo $praktikum['id']; ?>">
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">
                                Daftar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
