<?php
session_start();
require 'config.php';
requireLogin();

// Ambil statistik dashboard
$stats = getDashboardStats($pdo);

// Data untuk chart
$barang_data = $pdo->query("
    SELECT b.nama_barang, b.stok 
    FROM barang b 
    ORDER BY b.stok ASC 
    LIMIT 10
")->fetchAll();

// Notifikasi stok menipis
$notifications = getStokNotifications($pdo, 5);

// Data transaksi bulanan untuk chart
$transaksi_bulanan = $pdo->query("
    SELECT 
        m.bulan,
        COALESCE(m.total_masuk, 0) AS masuk,
        COALESCE(k.total_keluar, 0) AS keluar
    FROM (
        SELECT 
            MONTH(tanggal_masuk) AS bulan,
            SUM(jumlah) AS total_masuk
        FROM barang_masuk
        WHERE YEAR(tanggal_masuk) = YEAR(CURRENT_DATE())
        GROUP BY MONTH(tanggal_masuk)
    ) m
    LEFT JOIN (
        SELECT 
            MONTH(tanggal_keluar) AS bulan,
            SUM(jumlah) AS total_keluar
        FROM barang_keluar
        WHERE YEAR(tanggal_keluar) = YEAR(CURRENT_DATE())
        GROUP BY MONTH(tanggal_keluar)
    ) k ON m.bulan = k.bulan
    ORDER BY m.bulan
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Restock Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .notification-item {
            border-left: 4px solid #ffc107;
        }
        .notification-item.danger {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-boxes"></i> Restock Management
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['nama']) ?> 
                    <span class="badge bg-secondary"><?= htmlspecialchars($_SESSION['role']) ?></span>
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
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">
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
                    <a href="kategori.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tags"></i> Kategori
                    </a>
                    <a href="laporan.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="pengguna.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users"></i> Manajemen Pengguna
                    </a>
                    <?php endif; ?>
                    <a href="calculator.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-calculator"></i> Kalkulator Pintar
                    </a>
                </div>
                
                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Info Cepat</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">Total Nilai Stok:</small>
                        <h6><?= formatRupiah($stats['total_nilai_stok']) ?></h6>
                        <small class="text-muted">Barang Masuk (Bln Ini):</small>
                        <h6><?= $stats['barang_masuk_bulan_ini'] ?> item</h6>
                        <small class="text-muted">Barang Keluar (Bln Ini):</small>
                        <h6><?= $stats['barang_keluar_bulan_ini'] ?> item</h6>
                    </div>
                </div>
            </div>

            <!-- Konten -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <span class="text-muted"><?= date('l, d F Y') ?></span>
                </div>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>
                
                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?= $stats['total_barang'] ?></h4>
                                        <p>Total Barang</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-box fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?= $stats['total_stok'] ?></h4>
                                        <p>Total Stok</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-cubes fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?= $stats['barang_menipis'] ?></h4>
                                        <p>Stok Menipis</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?= $stats['barang_habis'] ?></h4>
                                        <p>Stok Habis</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-times-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts dan Notifikasi -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Grafik Stok Barang</h5>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-secondary active" onclick="showChart('stok')">Stok</button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="showChart('transaksi')">Transaksi</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="stokChart" width="400" height="200"></canvas>
                                <canvas id="transaksiChart" width="400" height="200" style="display: none;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Notifikasi Stok</h5>
                                <span class="badge bg-danger"><?= count($notifications) ?></span>
                            </div>
                            <div class="card-body">
                                <?php if (count($notifications) > 0): ?>
                                    <?php foreach ($notifications as $notif): ?>
                                        <div class="alert alert-warning py-2 notification-item <?= $notif['stok'] == 0 ? 'danger' : '' ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><?= htmlspecialchars($notif['nama_barang']) ?></span>
                                                <span class="badge <?= $notif['stok'] == 0 ? 'bg-danger' : 'bg-warning' ?>">
                                                    <?= $notif['stok'] ?> / <?= $notif['stok_minimum'] ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <a href="barang.php?filter=stok_menipis" class="btn btn-sm btn-outline-warning w-100">
                                        Lihat Semua
                                    </a>
                                <?php else: ?>
                                    <p class="text-muted text-center">Tidak ada notifikasi stok</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Aksi Cepat</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="barang_masuk.php" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus"></i> Tambah Barang Masuk
                                    </a>
                                    <a href="barang_keluar.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-minus"></i> Tambah Barang Keluar
                                    </a>
                                    <a href="laporan.php" class="btn btn-info btn-sm">
                                        <i class="fas fa-print"></i> Cetak Laporan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart Stok Barang
        const stokCtx = document.getElementById('stokChart').getContext('2d');
        const stokChart = new Chart(stokCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo '"' . implode('","', array_column($barang_data, 'nama_barang')) . '"'; ?>],
                datasets: [{
                    label: 'Stok Tersedia',
                    data: [<?php echo implode(',', array_column($barang_data, 'stok')); ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Stok'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Nama Barang'
                        }
                    }
                }
            }
        });

        // Chart Transaksi Bulanan
        const transaksiCtx = document.getElementById('transaksiChart').getContext('2d');
        const transaksiChart = new Chart(transaksiCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [
                    {
                        label: 'Barang Masuk',
                        data: [<?php
                            $data_masuk = array_fill(0, 12, 0);
                            foreach ($transaksi_bulanan as $t) {
                                $data_masuk[$t['bulan'] - 1] = $t['masuk'];
                            }
                            echo implode(',', $data_masuk);
                        ?>],
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.3
                    },
                    {
                        label: 'Barang Keluar',
                        data: [<?php
                            $data_keluar = array_fill(0, 12, 0);
                            foreach ($transaksi_bulanan as $t) {
                                $data_keluar[$t['bulan'] - 1] = $t['keluar'];
                            }
                            echo implode(',', $data_keluar);
                        ?>],
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Barang'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    }
                }
            }
        });

        // Fungsi toggle chart
        function showChart(type) {
            if (type === 'stok') {
                document.getElementById('stokChart').style.display = 'block';
                document.getElementById('transaksiChart').style.display = 'none';
            } else {
                document.getElementById('stokChart').style.display = 'none';
                document.getElementById('transaksiChart').style.display = 'block';
            }
            
            // Update button states
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Auto refresh notifikasi setiap 30 detik
        setInterval(() => {
            fetch('dashboard.php')
                .then(response => response.text())
                .then(html => {
                    // Implementasi update notifikasi
                    console.log('Dashboard updated');
                });
        }, 30000);
    </script>
</body>
</html>