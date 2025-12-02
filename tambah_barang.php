<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_barang = $_POST['kode_barang'];
    $nama_barang = $_POST['nama_barang'];
    $kategori_id = $_POST['kategori_id'];
    $supplier_id = $_POST['supplier_id'];
    $harga_beli = $_POST['harga_beli'];
    $harga_jual = $_POST['harga_jual'];

    $stmt = $pdo->prepare('INSERT INTO barang (kode_barang, nama_barang, kategori_id, supplier_id, harga_beli, harga_jual) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$kode_barang, $nama_barang, $kategori_id, $supplier_id, $harga_beli, $harga_jual]);

    header('Location: dashboard.php?success=Barang berhasil ditambahkan');
    exit;
}
?>