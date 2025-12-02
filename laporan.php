<?php
session_start();
require 'config.php';
requireLogin();

// Ambil data untuk laporan
$barang = $pdo->query("
    SELECT b.*, k.nama_kategori, s.nama_supplier 
    FROM barang b 
    LEFT JOIN kategori k ON b.kategori_id = k.id 
    LEFT JOIN supplier s ON b.supplier_id = s.id 
    ORDER BY b.nama_barang
")->fetchAll();

// Hitung total nilai stok
$total_nilai_stok = 0;
foreach ($barang as $b) {
    $total_nilai_stok += $b['stok'] * $b['harga_beli'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Restock Management</title>
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
                    <a href="supplier.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-truck"></i> Supplier
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#calculatorModal">
                        <i class="fas fa-calculator"></i> Kalkulator Pintar
                    </a>
                    <a href="laporan.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                <h2 class="mb-4">Laporan Stok Barang</h2>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Ringkasan</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3><?= count($barang) ?></h3>
                                        <p class="card-text">Total Jenis Barang</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3><?= array_sum(array_column($barang, 'stok')) ?></h3>
                                        <p class="card-text">Total Stok Barang</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3><?= formatRupiah($total_nilai_stok) ?></h3>
                                        <p class="card-text">Total Nilai Stok</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Detail Stok Barang</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
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
                                        <th>Nilai Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($barang as $b): 
                                        $nilai_stok = $b['stok'] * $b['harga_beli'];
                                    ?>
                                    <tr>
                                        <td><?= $b['kode_barang'] ?></td>
                                        <td><?= $b['nama_barang'] ?></td>
                                        <td><?= $b['nama_kategori'] ?></td>
                                        <td><?= $b['nama_supplier'] ?></td>
                                        <td>
                                            <span class="badge <?= $b['stok'] == 0 ? 'bg-danger' : ($b['stok'] <= $b['stok_minimum'] ? 'bg-warning' : 'bg-success') ?>">
                                                <?= $b['stok'] ?>
                                            </span>
                                        </td>
                                        <td><?= $b['stok_minimum'] ?></td>
                                        <td><?= formatRupiah($b['harga_beli']) ?></td>
                                        <td><?= formatRupiah($b['harga_jual']) ?></td>
                                        <td><?= formatRupiah($nilai_stok) ?></td>
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