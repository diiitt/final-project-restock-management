<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Konfigurasi database
$host = 'localhost';
$db   = 'restock_management';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk memeriksa login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Fungsi untuk cek role admin
function requireAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: dashboard.php?error=Akses ditolak');
        exit;
    }
}

// Fungsi untuk format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi untuk generate kode barang otomatis
function generateKodeBarang($pdo) {
    $stmt = $pdo->query("SELECT MAX(kode_barang) as last_code FROM barang WHERE kode_barang LIKE 'BRG%'");
    $result = $stmt->fetch();
    
    if ($result['last_code']) {
        $last_number = (int) substr($result['last_code'], 3);
        $new_number = $last_number + 1;
    } else {
        $new_number = 1;
    }
    
    return 'BRG' . str_pad($new_number, 3, '0', STR_PAD_LEFT);
}

// Fungsi untuk generate kode supplier otomatis
function generateKodeSupplier($pdo) {
    $stmt = $pdo->query("SELECT MAX(kode_supplier) as last_code FROM supplier WHERE kode_supplier LIKE 'SUP%'");
    $result = $stmt->fetch();
    
    if ($result['last_code']) {
        $last_number = (int) substr($result['last_code'], 3);
        $new_number = $last_number + 1;
    } else {
        $new_number = 1;
    }
    
    return 'SUP' . str_pad($new_number, 3, '0', STR_PAD_LEFT);
}

// Fungsi untuk validasi input
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk log aktivitas
function logActivity($pdo, $action, $table_name, $record_id = null, $old_values = null, $new_values = null) {
    $user_id = $_SESSION['user_id'] ?? null;
    
    $stmt = $pdo->prepare("
        INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user_id,
        $action,
        $table_name,
        $record_id,
        $old_values ? json_encode($old_values) : null,
        $new_values ? json_encode($new_values) : null
    ]);
}

// Fungsi untuk mendapatkan notifikasi stok menipis
function getStokNotifications($pdo, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT nama_barang, stok, stok_minimum 
        FROM barang 
        WHERE stok <= stok_minimum 
        ORDER BY stok ASC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Fungsi untuk mendapatkan statistik dashboard
function getDashboardStats($pdo) {
    $stats = [];
    
    // Total barang
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM barang");
    $stats['total_barang'] = $stmt->fetchColumn();
    
    // Total stok
    $stmt = $pdo->query("SELECT SUM(stok) as total FROM barang");
    $stats['total_stok'] = $stmt->fetchColumn() ?: 0;
    
    // Barang habis
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM barang WHERE stok = 0");
    $stats['barang_habis'] = $stmt->fetchColumn();
    
    // Barang menipis
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM barang WHERE stok <= stok_minimum AND stok > 0");
    $stats['barang_menipis'] = $stmt->fetchColumn();
    
    // Total nilai stok
    $stmt = $pdo->query("SELECT SUM(stok * harga_beli) as total FROM barang");
    $stats['total_nilai_stok'] = $stmt->fetchColumn() ?: 0;
    
    // Barang masuk bulan ini
    $stmt = $pdo->query("SELECT SUM(jumlah) as total FROM barang_masuk WHERE MONTH(tanggal_masuk) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_masuk) = YEAR(CURRENT_DATE())");
    $stats['barang_masuk_bulan_ini'] = $stmt->fetchColumn() ?: 0;
    
    // Barang keluar bulan ini
    $stmt = $pdo->query("SELECT SUM(jumlah) as total FROM barang_keluar WHERE MONTH(tanggal_keluar) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_keluar) = YEAR(CURRENT_DATE())");
    $stats['barang_keluar_bulan_ini'] = $stmt->fetchColumn() ?: 0;
    
    return $stats;
}
?>