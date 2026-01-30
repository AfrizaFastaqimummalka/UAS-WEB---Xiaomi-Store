<?php
session_start();
require_once 'includes/koneksi_database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Redirect jika cart kosong
if (empty($_SESSION['cart'])) {
    header("Location: keranjang.php");
    exit;
}

$success = '';
$error = '';

// Proses Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $nama = clean_input($_POST['nama']);
    $telepon = clean_input($_POST['telepon']);
    $alamat = clean_input($_POST['alamat']);
    $metode_pembayaran = clean_input($_POST['metode_pembayaran']);
    
    if (empty($nama) || empty($telepon) || empty($alamat)) {
        $error = "Semua field harus diisi!";
    } else {
        // Hitung total
        $total = 0;
        foreach ($_SESSION['cart'] as $id => $qty) {
            $result = $conn->query("SELECT harga FROM products WHERE id = $id");
            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
                $total += $product['harga'] * $qty;
            }
        }
        
        // Simpan order
        $stmt = $conn->prepare("INSERT INTO orders (nama_pembeli, telepon, alamat, total_harga, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssi", $nama, $telepon, $alamat, $total);
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // Simpan order items
            foreach ($_SESSION['cart'] as $id => $qty) {
                $result = $conn->query("SELECT nama, harga FROM products WHERE id = $id");
                if ($result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    $subtotal = $product['harga'] * $qty;
                    
                    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, nama_produk, harga, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_item->bind_param("iisiii", $order_id, $id, $product['nama'], $product['harga'], $qty, $subtotal);
                    $stmt_item->execute();
                }
            }
            
            // Kosongkan cart
            $_SESSION['cart'] = [];
            
            // Redirect ke success page
            $_SESSION['success_order'] = [
                'order_id' => $order_id,
                'nama' => $nama,
                'total' => $total
            ];
            header("Location: checkout.php?success=1");
            exit;
        } else {
            $error = "Terjadi kesalahan saat memproses pesanan!";
        }
        $stmt->close();
    }
}

// Ambil data cart untuk ditampilkan
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
    <title>Checkout - Xiaomi Store</title>
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
                <a href="keranjang.php">Keranjang (<?= $cart_count ?>)</a>
                <a href="?logout" style="color: #dc3545;">Logout</a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <?php if (isset($_GET['success']) && isset($_SESSION['success_order'])): ?>
            <!-- Success Message -->
            <div class="card p-3 text-center" style="max-width: 600px; margin: 60px auto;">
                <div style="font-size: 80px; margin-bottom: 20px;">✓</div>
                <h1 style="font-size: 36px; color: var(--orange); margin-bottom: 15px;">
                    Pesanan Berhasil!
                </h1>
                <p style="font-size: 18px; margin-bottom: 10px;">
                    Terima kasih, <strong><?= $_SESSION['success_order']['nama'] ?></strong>
                </p>
                <p style="color: #999; margin-bottom: 30px;">
                    Pesanan Anda dengan nomor <strong>#<?= $_SESSION['success_order']['order_id'] ?></strong> telah berhasil dibuat
                </p>
                
                <div style="background: rgba(255, 103, 0, 0.1); border: 2px solid var(--orange); padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                    <div style="color: #999; font-size: 14px; margin-bottom: 5px;">Total Pembayaran</div>
                    <div style="font-size: 32px; font-weight: 700; color: var(--orange);">
                        <?= format_rupiah($_SESSION['success_order']['total']) ?>
                    </div>
                </div>
                
                <div style="margin-bottom: 30px; padding: 20px; background: var(--grey); border-radius: 8px; text-align: left;">
                    <h3 style="margin-bottom: 15px; font-size: 18px;">Langkah Selanjutnya:</h3>
                    <ol style="margin-left: 20px; color: #999; line-height: 2;">
                        <li>Kami akan menghubungi Anda untuk konfirmasi pesanan</li>
                        <li>Setelah pembayaran dikonfirmasi, pesanan akan diproses</li>
                        <li>Produk akan dikirim ke alamat Anda dalam 1-2 hari</li>
                    </ol>
                </div>
                
                <div class="flex gap-2" style="justify-content: center;">
                    <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
                    <a href="daftar_produk.php" class="btn-secondary">Belanja Lagi</a>
                </div>
            </div>
            <?php unset($_SESSION['success_order']); ?>
        <?php else: ?>
            <!-- Checkout Form -->
            <div style="margin: 40px 0 30px;">
                <h1 style="font-size: 42px; color: var(--orange); margin-bottom: 5px;">
                    Checkout
                </h1>
                <p style="color: #999;">Lengkapi data untuk menyelesaikan pesanan</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="grid" style="grid-template-columns: 1.5fr 1fr; gap: 20px; align-items: start;">
                <!-- Left: Form -->
                <div class="card p-3">
                    <h2 style="font-size: 24px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid var(--orange);">
                        Informasi Pembeli
                    </h2>
                    
                    <form method="POST" action="">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
                        
                        <label>Nomor Telepon *</label>
                        <input type="tel" name="telepon" placeholder="08xx-xxxx-xxxx" required>
                        
                        <label>Alamat Lengkap *</label>
                        <textarea name="alamat" rows="4" placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Kota" required></textarea>
                        
                        <label>Metode Pembayaran</label>
                        <select name="metode_pembayaran">
                            <option value="transfer">Transfer Bank</option>
                            <option value="cod">COD (Cash on Delivery)</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="cicilan">Cicilan 0%</option>
                        </select>
                        
                        <div style="margin-top: 20px; padding: 15px; background: rgba(0, 123, 255, 0.1); border: 1px solid #007bff; border-radius: 4px;">
                            <div style="font-weight: 600; color: #007bff; margin-bottom: 8px;">ℹ️ Informasi Penting:</div>
                            <ul style="margin-left: 20px; color: #999; font-size: 13px; line-height: 1.8;">
                                <li>Pastikan data yang Anda masukkan benar</li>
                                <li>Kami akan menghubungi Anda untuk konfirmasi</li>
                                <li>Pengiriman GRATIS untuk area Surabaya</li>
                            </ul>
                        </div>
                        
                        <div class="flex gap-2 mt-3">
                            <button type="submit" name="checkout" class="btn" style="flex: 1; padding: 15px; font-size: 16px;">
                                Proses Pesanan →
                            </button>
                            <a href="keranjang.php" class="btn-secondary" style="flex: 1; padding: 15px; font-size: 16px; text-align: center;">
                                Kembali
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Right: Order Summary -->
                <div>
                    <div class="card p-2" style="position: sticky; top: 80px; margin-bottom: 20px;">
                        <h2 style="font-size: 20px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #333;">
                            Ringkasan Pesanan
                        </h2>
                        
                        <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                            <?php foreach ($cart_items as $item): ?>
                            <div style="display: flex; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #333;">
                                <div style="width: 60px; height: 60px; background: #0a0a0a; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <?php if (!empty($item['gambar']) && file_exists($item['gambar'])): ?>
                                        <img src="<?= $item['gambar'] ?>" alt="<?= $item['nama'] ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div style="font-size: 20px; color: #333; font-weight: 700;">Mi</div>
                                    <?php endif; ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; font-size: 14px; margin-bottom: 3px;"><?= $item['nama'] ?></div>
                                    <div style="color: #999; font-size: 12px; margin-bottom: 5px;"><?= $item['qty'] ?> x <?= format_rupiah($item['harga']) ?></div>
                                    <div style="color: var(--orange); font-weight: 600; font-size: 14px;"><?= format_rupiah($item['subtotal']) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <div class="flex-between" style="margin-bottom: 8px;">
                                <span style="color: #999; font-size: 14px;">Subtotal:</span>
                                <span style="font-weight: 600;"><?= format_rupiah($total) ?></span>
                            </div>
                            <div class="flex-between" style="margin-bottom: 8px;">
                                <span style="color: #999; font-size: 14px;">Ongkir:</span>
                                <span style="color: #28a745; font-weight: 600;">GRATIS</span>
                            </div>
                            <div class="flex-between" style="margin-bottom: 8px;">
                                <span style="color: #999; font-size: 14px;">Biaya Admin:</span>
                                <span style="color: #28a745; font-weight: 600;">GRATIS</span>
                            </div>
                        </div>
                        
                        <div style="padding-top: 15px; border-top: 2px solid var(--orange);">
                            <div class="flex-between">
                                <span style="font-size: 16px; font-weight: 700;">Total Pembayaran:</span>
                                <span style="font-size: 24px; font-weight: 700; color: var(--orange);">
                                    <?= format_rupiah($total) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card p-2">
                        <h3 style="font-size: 16px; margin-bottom: 15px;">Keuntungan Belanja di Xiaomi Store:</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; gap: 10px; align-items: start;">
                                <span style="color: var(--orange); font-size: 20px;">✓</span>
                                <div>
                                    <div style="font-weight: 600; font-size: 14px;">Garansi Resmi</div>
                                    <div style="color: #999; font-size: 12px;">TAM Indonesia</div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: start;">
                                <span style="color: var(--orange); font-size: 20px;">✓</span>
                                <div>
                                    <div style="font-weight: 600; font-size: 14px;">Gratis Ongkir</div>
                                    <div style="color: #999; font-size: 12px;">Area Surabaya</div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: start;">
                                <span style="color: var(--orange); font-size: 20px;">✓</span>
                                <div>
                                    <div style="font-weight: 600; font-size: 14px;">Cicilan 0%</div>
                                    <div style="color: #999; font-size: 12px;">Tersedia untuk semua produk</div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: start;">
                                <span style="color: var(--orange); font-size: 20px;">✓</span>
                                <div>
                                    <div style="font-weight: 600; font-size: 14px;">Kirim Cepat</div>
                                    <div style="color: #999; font-size: 12px;">1-2 hari sampai</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="height: 50px;"></div>
</body>
</html>
<?php $conn->close(); ?>