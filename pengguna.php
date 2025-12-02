<?php
session_start();
require 'config.php';
requireLogin();
requireAdmin();

// Tambah pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_pengguna'])) {
    $username = validateInput($_POST['username']);
    $password = validateInput($_POST['password']);
    $nama = validateInput($_POST['nama']);
    $role = validateInput($_POST['role']);

    // Cek username sudah ada
    $cek = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $cek->execute([$username]);
    
    if ($cek->fetchColumn() > 0) {
        header('Location: pengguna.php?error=Username sudah digunakan');
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $password_hash, $nama, $role]);
        
        logActivity($pdo, 'CREATE', 'users', $pdo->lastInsertId(), null, [
            'username' => $username,
            'nama' => $nama,
            'role' => $role
        ]);
        
        header('Location: pengguna.php?success=Pengguna berhasil ditambahkan');
        exit;
    } catch (PDOException $e) {
        header('Location: pengguna.php?error=Gagal menambahkan pengguna: ' . $e->getMessage());
        exit;
    }
}

// Hapus pengguna
if (isset($_GET['hapus'])) {
    $id = validateInput($_GET['hapus']);
    
    // Tidak boleh hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        header('Location: pengguna.php?error=Tidak dapat menghapus akun sendiri');
        exit;
    }

    try {
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        
        logActivity($pdo, 'DELETE', 'users', $id, null, null);
        
        header('Location: pengguna.php?success=Pengguna berhasil dihapus');
        exit;
    } catch (PDOException $e) {
        header('Location: pengguna.php?error=Gagal menghapus pengguna: ' . $e->getMessage());
        exit;
    }
}

// Ambil data pengguna
$pengguna = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Restock Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                    <span class="badge bg-primary"><?= htmlspecialchars($_SESSION['role']) ?></span>
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
                    <a href="supplier.php"