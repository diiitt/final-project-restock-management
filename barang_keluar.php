<?php
session_start();
require 'config.php';
requireLogin();

// Tambah barang keluar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_barang_keluar'])) {
    $barang_id = $_POST['barang_id'];
    $jumlah = $_POST['jumlah'];
    $tanggal_keluar = $_POST['tanggal_keluar'];

    // Ambil data barang
    $stmt = $pdo->prepare("SELECT stok, harga_jual FROM barang WHERE id = ?");
    $stmt->execute([$barang_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        header("Location: barang_keluar.php?error=Barang tidak ditemukan");
        exit;
    }

    $stok = $data['stok'];
    $harga_satuan = $data['harga_jual'];
    $total_harga = $jumlah * $harga_satuan;

    // Validasi stok
    if ($jumlah > $stok) {
        header("Location: barang_keluar.php?error=Stok tidak cukup! Stok tersedia: $stok");
        exit;
    }

    // Insert ke barang_keluar
    $stmt = $pdo->prepare("INSERT INTO barang_keluar 
        (barang_id, jumlah, harga_satuan, total_harga, tanggal_keluar) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$barang_id, $jumlah, $harga_satuan, $total_harga, $tanggal_keluar]);

    // Update stok barang
    $stmt = $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?");
    $stmt->execute([$jumlah, $barang_id]);

    header("Location: barang_keluar.php?success=Barang keluar berhasil dicatat");
    exit;
}

// Ambil data barang keluar
$barang_keluar = $pdo->query("
    SELECT bk.*, b.kode_barang, b.nama_barang
    FROM barang_keluar bk
    JOIN barang b ON bk.barang_id = b.id
    ORDER BY bk.tanggal_keluar DESC, bk.created_at DESC
")->fetchAll();

// Ambil barang untuk dropdown
$barang = $pdo->query("SELECT * FROM barang ORDER BY nama_barang")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Keluar - Restock Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand"><i class="fas fa-boxes"></i> Restock Management</a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3">Halo, <?= $_SESSION['nama'] ?></span>
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <div class="row">

        <div class="col-md-3">
            <div class="list-group">
                <a href="dashboard.php" class="list-group-item list-group-item-action"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="barang.php" class="list-group-item list-group-item-action"><i class="fas fa-box"></i> Data Barang</a>
                <a href="barang_masuk.php" class="list-group-item list-group-item-action"><i class="fas fa-arrow-down"></i> Barang Masuk</a>
                <a href="barang_keluar.php" class="list-group-item list-group-item-action active"><i class="fas fa-arrow-up"></i> Barang Keluar</a>
                <a href="supplier.php" class="list-group-item list-group-item-action"><i class="fas fa-truck"></i> Supplier</a>
                <a href="#" class="list-group-item list-group-item-action"><i class="fas fa-calculator"></i> Kalkulator Pintar</a>
                <a href="laporan.php" class="list-group-item list-group-item-action"><i class="fas fa-chart-bar"></i> Laporan</a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Barang Keluar</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahBarangKeluarModal">
                    <i class="fas fa-plus"></i> Tambah Barang Keluar
                </button>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?= $_GET['error'] ?></div>
            <?php endif; ?>

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
                                    <th>Harga Satuan</th>
                                    <th>Total Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($barang_keluar as $bk): ?>
                                    <tr>
                                        <td><?= $bk['tanggal_keluar'] ?></td>
                                        <td><?= $bk['kode_barang'] ?></td>
                                        <td><?= $bk['nama_barang'] ?></td>
                                        <td><?= $bk['jumlah'] ?></td>
                                        <td><?= number_format($bk['harga_satuan'], 0, ',', '.') ?></td>
                                        <td><?= number_format($bk['total_harga'], 0, ',', '.') ?></td>
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

<!-- Modal Tambah -->
<div class="modal fade" id="tambahBarangKeluarModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Barang Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="tambah_barang_keluar" value="1">

                    <div class="mb-3">
                        <label class="form-label">Barang</label>
                        <select class="form-select" name="barang_id" required>
                            <option value="">Pilih Barang</option>
                            <?php foreach ($barang as $b): ?>
                                <option value="<?= $b['id'] ?>">
                                    <?= $b['kode_barang'] ?> - <?= $b['nama_barang'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="jumlah" min="1" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Keluar</label>
                        <input type="date" name="tanggal_keluar" class="form-control" value="<?= date('Y-m-d') ?>" required>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
