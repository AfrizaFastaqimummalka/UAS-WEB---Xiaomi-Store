<?php
session_start();
require_once 'includes/koneksi_database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Ambil statistik
$total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$pending_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='pending'")->fetch_assoc()['total'];
$total_revenue = $conn->query("SELECT SUM(total_harga) as total FROM orders WHERE status='completed'")->fetch_assoc()['total'] ?? 0;

// Ambil cart count
$cart_count = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Xiaomi Store</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <a href="dashboard.php" class="logo">
                <span style="background: var(--orange); padding: 8px 15px; border-radius: 50%; margin-right: 10px;">Mi</span>
                XIAOMI STORE
            </a>
            
            <nav class="nav">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="daftar_produk.php">Produk</a>
                <a href="keranjang.php">Keranjang (<?= $cart_count ?>)</a>
                <a href="?logout" style="color: #dc3545;">Logout</a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <div style="margin: 40px 0 20px;">
            <h1 style="font-size: 42px; color: var(--orange); margin-bottom: 10px;">
                Dashboard Admin
            </h1>
            <p style="color: #999;">Selamat datang, <strong><?= $_SESSION['username'] ?></strong>!</p>
        </div>
        
        <!-- Statistik Cards -->
        <div class="grid grid-4" style="margin-bottom: 40px;">
            <div class="card p-2">
                <div style="font-size: 36px; margin-bottom: 10px;">📦</div>
                <h3 style="color: var(--orange); font-size: 32px; margin-bottom: 5px;"><?= $total_products ?></h3>
                <p style="color: #999; font-size: 14px;">Total Produk</p>
            </div>
            
            <div class="card p-2">
                <div style="font-size: 36px; margin-bottom: 10px;">🛍️</div>
                <h3 style="color: var(--orange); font-size: 32px; margin-bottom: 5px;"><?= $total_orders ?></h3>
                <p style="color: #999; font-size: 14px;">Total Pesanan</p>
            </div>
            
            <div class="card p-2">
                <div style="font-size: 36px; margin-bottom: 10px;">⏳</div>
                <h3 style="color: var(--orange); font-size: 32px; margin-bottom: 5px;"><?= $pending_orders ?></h3>
                <p style="color: #999; font-size: 14px;">Pesanan Pending</p>
            </div>
            
            <div class="card p-2">
                <div style="font-size: 36px; margin-bottom: 10px;">💰</div>
                <h3 style="color: var(--orange); font-size: 24px; margin-bottom: 5px;"><?= format_rupiah($total_revenue) ?></h3>
                <p style="color: #999; font-size: 14px;">Total Pendapatan</p>
            </div>
        </div>
        
        <!-- Menu Cards -->
        <h2 style="font-size: 28px; margin-bottom: 20px; color: white;">Menu Utama</h2>
        <div class="dashboard-grid">
            <a href="daftar_produk.php" class="dashboard-card">
                <div class="icon">📱</div>
                <h3>Kelola Produk</h3>
                <p>Tambah, edit, dan hapus produk</p>
            </a>
            
            <a href="keranjang.php" class="dashboard-card">
                <div class="icon">🛒</div>
                <h3>Keranjang Belanja</h3>
                <p>Lihat dan kelola keranjang (<?= $cart_count ?> item)</p>
            </a>
            
            <a href="checkout.php" class="dashboard-card">
                <div class="icon">💳</div>
                <h3>Checkout</h3>
                <p>Proses pembayaran dan checkout</p>
            </a>
            
            
        </div>
        
        <!-- Recent Orders -->
        <div style="margin-top: 50px;">
            <h2 style="font-size: 28px; margin-bottom: 20px; color: white;">Pesanan Terbaru</h2>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Pembeli</th>
                            <th>Telepon</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent_orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
                        if ($recent_orders->num_rows > 0):
                            while ($order = $recent_orders->fetch_assoc()):
                        ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= $order['nama_pembeli'] ?></td>
                            <td><?= $order['telepon'] ?></td>
                            <td style="color: var(--orange); font-weight: 600;"><?= format_rupiah($order['total_harga']) ?></td>
                            <td>
                                <span style="padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;
                                    <?php
                                    switch($order['status']) {
                                        case 'pending':
                                            echo 'background: rgba(255, 193, 7, 0.2); color: #ffc107;';
                                            break;
                                        case 'processing':
                                            echo 'background: rgba(0, 123, 255, 0.2); color: #007bff;';
                                            break;
                                        case 'completed':
                                            echo 'background: rgba(40, 167, 69, 0.2); color: #28a745;';
                                            break;
                                        case 'cancelled':
                                            echo 'background: rgba(220, 53, 69, 0.2); color: #dc3545;';
                                            break;
                                    }
                                    ?>
                                ">
                                    <?= strtoupper($order['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 40px; color: #999;">
                                Belum ada pesanan
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div style="height: 50px;"></div>
</body>
</html>
<?php $conn->close(); ?>