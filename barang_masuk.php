<?php
session_start();
require 'config.php';
requireLogin();

// Tambah barang masuk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_barang_masuk'])) {
    $barang_id = $_POST['barang_id'];
    $jumlah = $_POST['jumlah'];
    $tanggal_masuk = $_POST['tanggal_masuk'];

    // Insert ke tabel barang_masuk
// Insert ke tabel barang_masuk
$harga_satuan = 0;
$total_harga = $jumlah * $harga_satuan;

$stmt = $pdo->prepare('INSERT INTO barang_masuk 
    (barang_id, jumlah, tanggal_masuk, harga_satuan, total_harga) 
    VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$barang_id, $jumlah, $tanggal_masuk, $harga_satuan, $total_harga]);

// UPDATE STOK BARANG
$update = $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id = ?");
$update->execute([$jumlah, $barang_id]);

// Redirect agar tidak double submit
header("Location: barang_masuk.php?success=Data+berhasil+ditambahkan");
exit;


}

// Ambil data barang masuk
$barang_masuk = $pdo->query("
    SELECT bm.*, b.kode_barang, b.nama_barang 
    FROM barang_masuk bm 
    JOIN barang b ON bm.barang_id = b.id 
    ORDER BY bm.tanggal_masuk DESC, bm.created_at DESC
")->fetchAll();

// Ambil data barang untuk dropdown
$barang = $pdo->query("SELECT * FROM barang ORDER BY nama_barang")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Masuk - Restock Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-boxes"></i> Restock Management
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Halo, <?= $_SESSION['nama'] ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="barang.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-box"></i> Data Barang
                    </a>
                    <a href="barang_masuk.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-arrow-down"></i> Barang Masuk
                    </a>
                    <a href="barang_keluar.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-arrow-up"></i> Barang Keluar
                    </a>
                    <a href="supplier.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-truck"></i> Supplier
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#calculatorModal">
                        <i class="fas fa-calculator"></i> Kalkulator Pintar
                    </a>
                    <a href="laporan.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Barang Masuk</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahBarangMasukModal">
                        <i class="fas fa-plus"></i> Tambah Barang Masuk
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?= $_GET['success'] ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($barang_masuk as $bm): ?>
                                    <tr>
                                        <td><?= $bm['tanggal_masuk'] ?></td>
                                        <td><?= $bm['kode_barang'] ?></td>
                                        <td><?= $bm['nama_barang'] ?></td>
                                        <td><?= $bm['jumlah'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Barang Masuk -->
    <div class="modal fade" id="tambahBarangMasukModal" tabindex="-1" aria-labelledby="tambahBarangMasukModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahBarangMasukModalLabel">Tambah Barang Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="tambah_barang_masuk" value="1">
                        <div class="mb-3">
                            <label for="barang_id" class="form-label">Barang</label>
                            <select class="form-select" id="barang_id" name="barang_id" required>
                                <option value="">Pilih Barang</option>
                                <?php foreach ($barang as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= $b['kode_barang'] ?> - <?= $b['nama_barang'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" required>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                            <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Kalkulator (sama seperti sebelumnya) -->
    <div class="modal fade" id="calculatorModal" tabindex="-1" aria-labelledby="calculatorModalLabel" aria-hidden="true">
        <!-- ... isi modal kalkulator ... -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Kalkulator (sama seperti sebelumnya)
    </script>
</body>
</html>