<?php
session_start();
require 'config.php';
requireLogin();

// Tambah barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_barang'])) {
    $kode_barang = validateInput($_POST['kode_barang']);
    $nama_barang = validateInput($_POST['nama_barang']);
    $kategori_id = validateInput($_POST['kategori_id']);
    $supplier_id = validateInput($_POST['supplier_id']);
    $stok_minimum = validateInput($_POST['stok_minimum']);
    $harga_beli = validateInput($_POST['harga_beli']);
    $harga_jual = validateInput($_POST['harga_jual']);

    // Validasi harga jual harus lebih besar dari harga beli
    if ($harga_jual <= $harga_beli) {
        header('Location: barang.php?error=Harga jual harus lebih besar dari harga beli');
        exit;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO barang (kode_barang, nama_barang, kategori_id, supplier_id, stok_minimum, harga_beli, harga_jual) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$kode_barang, $nama_barang, $kategori_id, $supplier_id, $stok_minimum, $harga_beli, $harga_jual]);
        
        header('Location: barang.php?success=Barang berhasil ditambahkan');
        exit;
    } catch (PDOException $e) {
        header('Location: barang.php?error=Gagal menambahkan barang: ' . $e->getMessage());
        exit;
    }
}

// Edit barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_barang'])) {
    $id = validateInput($_POST['id']);
    $kode_barang = validateInput($_POST['kode_barang']);
    $nama_barang = validateInput($_POST['nama_barang']);
    $kategori_id = validateInput($_POST['kategori_id']);
    $supplier_id = validateInput($_POST['supplier_id']);
    $stok_minimum = validateInput($_POST['stok_minimum']);
    $harga_beli = validateInput($_POST['harga_beli']);
    $harga_jual = validateInput($_POST['harga_jual']);

    if ($harga_jual <= $harga_beli) {
        header('Location: barang.php?error=Harga jual harus lebih besar dari harga beli');
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE barang SET kode_barang=?, nama_barang=?, kategori_id=?, supplier_id=?, stok_minimum=?, harga_beli=?, harga_jual=? WHERE id=?');
        $stmt->execute([$kode_barang, $nama_barang, $kategori_id, $supplier_id, $stok_minimum, $harga_beli, $harga_jual, $id]);
        
        header('Location: barang.php?success=Barang berhasil diupdate');
        exit;
    } catch (PDOException $e) {
        header('Location: barang.php?error=Gagal mengupdate barang: ' . $e->getMessage());
        exit;
    }
}

// Hapus barang
if (isset($_GET['hapus'])) {
    $id = validateInput($_GET['hapus']);
    
    // Cek apakah barang ada di transaksi
    $cek_masuk = $pdo->prepare('SELECT COUNT(*) FROM barang_masuk WHERE barang_id = ?');
    $cek_masuk->execute([$id]);
    $cek_keluar = $pdo->prepare('SELECT COUNT(*) FROM barang_keluar WHERE barang_id = ?');
    $cek_keluar->execute([$id]);
    
    if ($cek_masuk->fetchColumn() > 0 || $cek_keluar->fetchColumn() > 0) {
        header('Location: barang.php?error=Barang tidak dapat dihapus karena terdapat transaksi terkait');
        exit;
    }

    try {
        $pdo->prepare('DELETE FROM barang WHERE id = ?')->execute([$id]);
        header('Location: barang.php?success=Barang berhasil dihapus');
        exit;
    } catch (PDOException $e) {
        header('Location: barang.php?error=Gagal menghapus barang: ' . $e->getMessage());
        exit;
    }
}

// Ambil data barang
$barang = $pdo->query("
    SELECT b.*, k.nama_kategori, s.nama_supplier 
    FROM barang b 
    LEFT JOIN kategori k ON b.kategori_id = k.id 
    LEFT JOIN supplier s ON b.supplier_id = s.id 
    ORDER BY b.nama_barang
")->fetchAll();

// Ambil data kategori dan supplier untuk form
$kategori = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$supplier = $pdo->query("SELECT * FROM supplier ORDER BY nama_supplier")->fetchAll();

// Generate kode barang otomatis
$kode_otomatis = generateKodeBarang($pdo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Restock Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-boxes"></i> Restock Management
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Halo, <?= htmlspecialchars($_SESSION['nama']) ?>
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
                    <a href="barang.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-box"></i> Data Barang
                    </a>
                    <a href="barang_masuk.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-arrow-down"></i> Barang Masuk
                    </a>
                    <a href="barang_keluar.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-arrow-up"></i> Barang Keluar
                    </a>
                    <a href="supplier.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-truck"></i> Supplier
                    </a>
                    <a href="kategori.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tags"></i> Kategori
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
                    <h2>Data Barang</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahBarangModal">
                        <i class="fas fa-plus"></i> Tambah Barang
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="barangTable">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>Kategori</th>
                                        <th>Supplier</th>
                                        <th>Stok</th>
                                        <th>Stok Min</th>
                                        <th>Harga Beli</th>
                                        <th>Harga Jual</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($barang as $b): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b['kode_barang']) ?></td>
                                        <td><?= htmlspecialchars($b['nama_barang']) ?></td>
                                        <td><?= htmlspecialchars($b['nama_kategori']) ?></td>
                                        <td><?= htmlspecialchars($b['nama_supplier']) ?></td>
                                        <td>
                                            <span class="badge <?= $b['stok'] == 0 ? 'bg-danger' : ($b['stok'] <= $b['stok_minimum'] ? 'bg-warning' : 'bg-success') ?>">
                                                <?= $b['stok'] ?>
                                            </span>
                                        </td>
                                        <td><?= $b['stok_minimum'] ?></td>
                                        <td><?= formatRupiah($b['harga_beli']) ?></td>
                                        <td><?= formatRupiah($b['harga_jual']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editBarang(<?= htmlspecialchars(json_encode($b)) ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="barang.php?hapus=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus barang <?= htmlspecialchars($b['nama_barang']) ?>?')">
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

    <!-- Modal Tambah Barang -->
    <div class="modal fade" id="tambahBarangModal" tabindex="-1" aria-labelledby="tambahBarangModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahBarangModalLabel">Tambah Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="tambah_barang" value="1">
                        <div class="mb-3">
                            <label for="kode_barang" class="form-label">Kode Barang</label>
                            <input type="text" class="form-control" id="kode_barang" name="kode_barang" value="<?= $kode_otomatis ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_barang" class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                        </div>
                        <div class="mb-3">
                            <label for="kategori_id" class="form-label">Kategori</label>
                            <select class="form-select" id="kategori_id" name="kategori_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="supplier_id" class="form-label">Supplier</label>
                            <select class="form-select" id="supplier_id" name="supplier_id" required>
                                <option value="">Pilih Supplier</option>
                                <?php foreach ($supplier as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_supplier']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="stok_minimum" class="form-label">Stok Minimum</label>
                            <input type="number" class="form-control" id="stok_minimum" name="stok_minimum" value="10" min="0" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="harga_beli" class="form-label">Harga Beli</label>
                                    <input type="number" class="form-control" id="harga_beli" name="harga_beli" step="100" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="harga_jual" class="form-label">Harga Jual</label>
                                    <input type="number" class="form-control" id="harga_jual" name="harga_jual" step="100" min="0" required>
                                </div>
                            </div>
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

    <!-- Modal Edit Barang -->
    <div class="modal fade" id="editBarangModal" tabindex="-1" aria-labelledby="editBarangModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBarangModalLabel">Edit Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="edit_barang" value="1">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_kode_barang" class="form-label">Kode Barang</label>
                            <input type="text" class="form-control" id="edit_kode_barang" name="kode_barang" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nama_barang" class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" id="edit_nama_barang" name="nama_barang" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_kategori_id" class="form-label">Kategori</label>
                            <select class="form-select" id="edit_kategori_id" name="kategori_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_supplier_id" class="form-label">Supplier</label>
                            <select class="form-select" id="edit_supplier_id" name="supplier_id" required>
                                <option value="">Pilih Supplier</option>
                                <?php foreach ($supplier as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_supplier']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_stok_minimum" class="form-label">Stok Minimum</label>
                            <input type="number" class="form-control" id="edit_stok_minimum" name="stok_minimum" min="0" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_harga_beli" class="form-label">Harga Beli</label>
                                    <input type="number" class="form-control" id="edit_harga_beli" name="harga_beli" step="100" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_harga_jual" class="form-label">Harga Jual</label>
                                    <input type="number" class="form-control" id="edit_harga_jual" name="harga_jual" step="100" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Inisialisasi DataTable
        $(document).ready(function() {
            $('#barangTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
                }
            });
        });

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

        // Fungsi edit barang
        function editBarang(barang) {
            document.getElementById('edit_id').value = barang.id;
            document.getElementById('edit_kode_barang').value = barang.kode_barang;
            document.getElementById('edit_nama_barang').value = barang.nama_barang;
            document.getElementById('edit_kategori_id').value = barang.kategori_id;
            document.getElementById('edit_supplier_id').value = barang.supplier_id;
            document.getElementById('edit_stok_minimum').value = barang.stok_minimum;
            document.getElementById('edit_harga_beli').value = barang.harga_beli;
            document.getElementById('edit_harga_jual').value = barang.harga_jual;
            
            var editModal = new bootstrap.Modal(document.getElementById('editBarangModal'));
            editModal.show();
        }

        // Validasi harga jual
        document.addEventListener('DOMContentLoaded', function() {
            const hargaBeli = document.getElementById('harga_beli');
            const hargaJual = document.getElementById('harga_jual');
            const editHargaBeli = document.getElementById('edit_harga_beli');
            const editHargaJual = document.getElementById('edit_harga_jual');

            function validateHarga() {
                if (parseFloat(hargaJual.value) <= parseFloat(hargaBeli.value)) {
                    hargaJual.setCustomValidity('Harga jual harus lebih besar dari harga beli');
                } else {
                    hargaJual.setCustomValidity('');
                }
            }

            function validateEditHarga() {
                if (parseFloat(editHargaJual.value) <= parseFloat(editHargaBeli.value)) {
                    editHargaJual.setCustomValidity('Harga jual harus lebih besar dari harga beli');
                } else {
                    editHargaJual.setCustomValidity('');
                }
            }

            if (hargaBeli && hargaJual) {
                hargaBeli.addEventListener('input', validateHarga);
                hargaJual.addEventListener('input', validateHarga);
            }

            if (editHargaBeli && editHargaJual) {
                editHargaBeli.addEventListener('input', validateEditHarga);
                editHargaJual.addEventListener('input', validateEditHarga);
            }
        });
    </script>
</body>
</html>