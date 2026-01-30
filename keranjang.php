<?php
session_start();
require_once 'includes/koneksi_database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Inisialisasi cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$success = '';
$error = '';

// Tambah ke cart
if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    
    // Cek apakah produk ada
    $check = $conn->query("SELECT id FROM products WHERE id = $product_id");
    if ($check->num_rows > 0) {
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
        $success = "Produk berhasil ditambahkan ke keranjang!";
    } else {
        $error = "Produk tidak ditemukan!";
    }
}

// Update quantity
if (isset($_GET['update']) && isset($_GET['qty'])) {
    $product_id = (int)$_GET['update'];
    $qty = (int)$_GET['qty'];
    
    if ($qty > 0) {
        $_SESSION['cart'][$product_id] = $qty;
        $success = "Jumlah produk berhasil diupdate!";
    } else {
        unset($_SESSION['cart'][$product_id]);
        $success = "Produk dihapus dari keranjang!";
    }
}

// Hapus dari cart
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
    $success = "Produk berhasil dihapus dari keranjang!";
}

// Kosongkan cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $success = "Keranjang berhasil dikosongkan!";
}

// Ambil data cart
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        $result = $conn->query("SELECT * FROM products WHERE id = $id");
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $product['qty'] = $qty;
            $product['subtotal'] = $product['harga'] * $qty;
            $cart_items[] = $product;
            $total += $product['subtotal'];
        }
    }
}

$cart_count = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Xiaomi Store</title>
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
                <a href="dashboard.php">Dashboard</a>
                <a href="daftar_produk.php">Produk</a>
                <a href="keranjang.php" class="active">Keranjang (<?= $cart_count ?>)</a>
                <a href="?logout" style="color: #dc3545;">Logout</a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <div style="margin: 40px 0 30px;">
            <h1 style="font-size: 42px; color: var(--orange); margin-bottom: 5px;">
                Keranjang Belanja
            </h1>
            <p style="color: #999;">Total <?= $cart_count ?> item dalam keranjang</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div class="card p-3 text-center">
                <div style="font-size: 80px; margin-bottom: 20px;">🛒</div>
                <h2 style="font-size: 28px; margin-bottom: 10px;">Keranjang Kosong</h2>
                <p style="color: #999; margin-bottom: 30px;">Belum ada produk dalam keranjang Anda</p>
                <a href="daftar_produk.php" class="btn">Lihat Produk</a>
            </div>
        <?php else: ?>
            <!-- Cart Items -->
            <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 20px; align-items: start;">
                <!-- Left: Cart Items List -->
                <div class="card">
                    <div style="padding: 20px; border-bottom: 2px solid var(--orange);">
                        <div class="flex-between">
                            <h2 style="font-size: 20px;">Produk (<?= count($cart_items) ?>)</h2>
                            <a href="?clear" onclick="return confirm('Kosongkan semua keranjang?')" style="color: #dc3545; text-decoration: none; font-size: 14px;">
                                Kosongkan Keranjang
                            </a>
                        </div>
                    </div>
                    
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <?php if (!empty($item['gambar']) && file_exists($item['gambar'])): ?>
                                <img src="<?= $item['gambar'] ?>" alt="<?= $item['nama'] ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <div style="width: 80px; height: 80px; background: #0a0a0a; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 24px; color: #333; font-weight: 700;">
                                    Mi
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cart-item-info">
                            <div class="cart-item-name"><?= $item['nama'] ?></div>
                            <div class="cart-item-price"><?= format_rupiah($item['harga']) ?></div>
                            <div style="color: #666; font-size: 12px; margin-top: 5px;"><?= $item['kategori'] ?></div>
                        </div>
                        
                        <div class="cart-item-actions">
                            <div class="qty-control">
                                <a href="?update=<?= $item['id'] ?>&qty=<?= $item['qty'] - 1 ?>" style="text-decoration: none;">
                                    <button <?= $item['qty'] <= 1 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>-</button>
                                </a>
                                <span style="min-width: 30px; text-align: center; font-weight: 600;"><?= $item['qty'] ?></span>
                                <a href="?update=<?= $item['id'] ?>&qty=<?= $item['qty'] + 1 ?>" style="text-decoration: none;">
                                    <button>+</button>
                                </a>
                            </div>
                            
                            <div style="text-align: right; min-width: 120px;">
                                <div style="font-weight: 700; color: var(--orange); font-size: 16px;">
                                    <?= format_rupiah($item['subtotal']) ?>
                                </div>
                                <a href="?remove=<?= $item['id'] ?>" onclick="return confirm('Hapus <?= addslashes($item['nama']) ?> dari keranjang?')" style="color: #dc3545; font-size: 12px; text-decoration: none;">
                                    Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Right: Summary -->
                <div class="card p-2" style="position: sticky; top: 80px;">
                    <h2 style="font-size: 20px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #333;">
                        Ringkasan Belanja
                    </h2>
                    
                    <div style="margin-bottom: 20px;">
                        <div class="flex-between" style="margin-bottom: 10px;">
                            <span style="color: #999;">Total Item:</span>
                            <span style="font-weight: 600;"><?= $cart_count ?> item</span>
                        </div>
                        
                        <div class="flex-between" style="margin-bottom: 10px;">
                            <span style="color: #999;">Subtotal:</span>
                            <span style="font-weight: 600;"><?= format_rupiah($total) ?></span>
                        </div>
                        
                        <div class="flex-between" style="margin-bottom: 10px;">
                            <span style="color: #999;">Ongkir:</span>
                            <span style="color: #28a745; font-weight: 600;">GRATIS</span>
                        </div>
                    </div>
                    
                    <div style="padding-top: 15px; border-top: 2px solid var(--orange); margin-bottom: 20px;">
                        <div class="flex-between">
                            <span style="font-size: 18px; font-weight: 700;">Total:</span>
                            <span style="font-size: 24px; font-weight: 700; color: var(--orange);">
                                <?= format_rupiah($total) ?>
                            </span>
                        </div>
                    </div>
                    
                    <a href="checkout.php" class="btn" style="width: 100%; text-align: center; padding: 15px; font-size: 16px;">
                        Lanjut ke Checkout →
                    </a>
                    
                    <a href="daftar_produk.php" class="btn-secondary" style="width: 100%; text-align: center; padding: 12px; margin-top: 10px; display: block;">
                        Lanjut Belanja
                    </a>
                    
                    <div style="margin-top: 20px; padding: 15px; background: rgba(40, 167, 69, 0.1); border: 1px solid #28a745; border-radius: 4px; font-size: 13px;">
                        <div style="font-weight: 600; color: #28a745; margin-bottom: 5px;">✓ Keuntungan Berbelanja:</div>
                        <ul style="margin-left: 20px; color: #999;">
                            <li>Garansi Resmi TAM</li>
                            <li>Gratis Ongkir</li>
                            <li>Cicilan 0%</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="height: 50px;"></div>
</body>
</html>
<?php $conn->close(); ?>