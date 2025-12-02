<?php
session_start();
require 'config.php';
requireLogin();

// Tambah supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_supplier'])) {
    $nama_supplier = $_POST['nama_supplier'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];

    $kode_supplier = "SUP" . time(); // generate kode otomatis

$stmt = $pdo->prepare('INSERT INTO supplier (kode_supplier, nama_supplier, alamat, telepon) VALUES (?, ?, ?, ?)');
$stmt->execute([$kode_supplier, $nama_supplier, $alamat, $telepon]);

    
    header('Location: supplier.php?success=Supplier berhasil ditambahkan');
    exit;
}

// Hapus supplier
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Cek apakah supplier digunakan di barang
    $cek = $pdo->prepare('SELECT COUNT(*) FROM barang WHERE supplier_id = ?');
    $cek->execute([$id]);
    $count = $cek->fetchColumn();

    if ($count > 0) {
        header('Location: supplier.php?error=Supplier tidak dapat dihapus karena masih digunakan di data barang');
        exit;
    }

    $pdo->prepare('DELETE FROM supplier WHERE id = ?')->execute([$id]);
    header('Location: supplier.php?success=Supplier berhasil dihapus');
    exit;
}

// Ambil data supplier
$supplier = $pdo->query("SELECT * FROM supplier ORDER BY nama_supplier")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier - Restock Management</title>
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
                    <a href="barang_masuk.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-arrow-down"></i> Barang Masuk
                    </a>
                    <a href="barang_keluar.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-arrow-up"></i> Barang Keluar
                    </a>
                    <a href="supplier.php" class="list-group-item list-group-item-action active">
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
                    <h2>Data Supplier</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahSupplierModal">
                        <i class="fas fa-plus"></i> Tambah Supplier
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?= $_GET['success'] ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?= $_GET['error'] ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nama Supplier</th>
                                        <th>Alamat</th>
                                        <th>Telepon</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($supplier as $s): ?>
                                    <tr>
                                        <td><?= $s['nama_supplier'] ?></td>
                                        <td><?= $s['alamat'] ?></td>
                                        <td><?= $s['telepon'] ?></td>
                                        <td>
                                            <a href="supplier.php?hapus=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
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

    <!-- Modal Tambah Supplier -->
    <div class="modal fade" id="tambahSupplierModal" tabindex="-1" aria-labelledby="tambahSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahSupplierModalLabel">Tambah Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="tambah_supplier" value="1">
                        <div class="mb-3">
                            <label for="nama_supplier" class="form-label">Nama Supplier</label>
                            <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="telepon" class="form-label">Telepon</label>
                            <input type="text" class="form-control" id="telepon" name="telepon">
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

    <!-- Modal Kalkulator -->
    <div class="modal fade" id="calculatorModal" tabindex="-1" aria-labelledby="calculatorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calculatorModalLabel">Kalkulator Pintar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="calculator">
                        <input type="text" class="form-control mb-2 text-end" id="calcDisplay" readonly value="0">
                        <div class="row g-1 mb-1">
                            <div class="col-3"><button class="btn btn-secondary w-100" onclick="clearDisplay()">C</button></div>
                            <div class="col-3"><button class="btn btn-secondary w-100" onclick="deleteLast()">⌫</button></div>
                            <div class="col-3"><button class="btn btn-secondary w-100" onclick="appendToDisplay('/')">/</button></div>
                            <div class="col-3"><button class="btn btn-secondary w-100" onclick="appendToDisplay('*')">×</button></div>
                        </div>
                        <div class="row g-1 mb-1">
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('7')">7</button></div>
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('8')">8</button></div>
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('9')">9</button></div>
                            <div class="col-3"><button class="btn btn-secondary w-100" onclick="appendToDisplay('-')">-</button></div>
                        </div>
                        <div class="row g-1 mb-1">
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('4')">4</button></div>
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('5')">5</button></div>
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('6')">6</button></div>
                            <div class="col-3"><button class="btn btn-secondary w-100" onclick="appendToDisplay('+')">+</button></div>
                        </div>
                        <div class="row g-1 mb-1">
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('1')">1</button></div>
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('2')">2</button></div>
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('3')">3</button></div>
                            <div class="col-3"><button class="btn btn-success w-100" onclick="calculate()">=</button></div>
                        </div>
                        <div class="row g-1">
                            <div class="col-6"><button class="btn btn-light w-100" onclick="appendToDisplay('0')">0</button></div>
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('.')">.</button></div>
                            <div class="col-3"><button class="btn btn-light w-100" onclick="appendToDisplay('00')">00</button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Kalkulator
        let display = document.getElementById('calcDisplay');

        function appendToDisplay(value) {
            if (display.value === '0' || display.value === 'Error') {
                display.value = value;
            } else {
                display.value += value;
            }
        }

        function clearDisplay() {
            display.value = '0';
        }

        function deleteLast() {
            display.value = display.value.slice(0, -1);
            if (display.value === '') display.value = '0';
        }

        function calculate() {
            try {
                display.value = eval(display.value.replace('×', '*'));
            } catch (error) {
                display.value = 'Error';
            }
        }
    </script>
</body>
</html>