<?php
session_start();
require_once 'includes/koneksi_database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// AJAX Handler untuk CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add':
            $nama = clean_input($_POST['nama']);
            $harga = (int)$_POST['harga'];
            $deskripsi = clean_input($_POST['deskripsi']);
            $gambar = clean_input($_POST['gambar']);
            $kategori = clean_input($_POST['kategori']);
            
            $stmt = $conn->prepare("INSERT INTO products (nama, harga, deskripsi, gambar, kategori) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $nama, $harga, $deskripsi, $gambar, $kategori);
            echo json_encode(['ok' => $stmt->execute()]);
            $stmt->close();
            exit;
            
        case 'update':
            $id = (int)$_POST['id'];
            $nama = clean_input($_POST['nama']);
            $harga = (int)$_POST['harga'];
            $deskripsi = clean_input($_POST['deskripsi']);
            $gambar = clean_input($_POST['gambar']);
            $kategori = clean_input($_POST['kategori']);
            
            $stmt = $conn->prepare("UPDATE products SET nama=?, harga=?, deskripsi=?, gambar=?, kategori=? WHERE id=?");
            $stmt->bind_param("sisssi", $nama, $harga, $deskripsi, $gambar, $kategori, $id);
            echo json_encode(['ok' => $stmt->execute()]);
            $stmt->close();
            exit;
            
        case 'delete':
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
            $stmt->bind_param("i", $id);
            echo json_encode(['ok' => $stmt->execute()]);
            $stmt->close();
            exit;
    }
}

// Ambil semua produk
$products = $conn->query("SELECT * FROM products ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$cart_count = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk - Xiaomi Store</title>
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
                <a href="daftar_produk.php" class="active">Produk</a>
                <a href="keranjang.php">Keranjang (<?= $cart_count ?>)</a>
                <a href="?logout" style="color: #dc3545;">Logout</a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <div class="flex-between" style="margin: 40px 0 30px;">
            <div>
                <h1 style="font-size: 42px; color: var(--orange); margin-bottom: 5px;">
                    Daftar Produk
                </h1>
                <p style="color: #999;">Kelola semua produk Xiaomi Store</p>
            </div>
            <button onclick="openAddModal()" class="btn">
                + Tambah Produk
            </button>
        </div>
        
        <!-- Product Grid -->
        <div class="grid grid-3">
            <?php foreach ($products as $p): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if (!empty($p['gambar']) && file_exists($p['gambar'])): ?>
                        <img src="<?= $p['gambar'] ?>" alt="<?= $p['nama'] ?>" style="width: 100%; height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div style="font-size: 48px; color: #333; font-weight: 700;">Mi</div>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <div class="product-name"><?= $p['nama'] ?></div>
                    <div class="product-desc"><?= $p['deskripsi'] ?></div>
                    <div class="product-price"><?= format_rupiah($p['harga']) ?></div>
                    
                    <div style="display: flex; gap: 10px;">
                        <a href="keranjang.php?add=<?= $p['id'] ?>" class="btn" style="flex: 1; text-align: center; font-size: 14px; padding: 10px;">
                            + Keranjang
                        </a>
                        <button onclick='editProduct(<?= json_encode($p) ?>)' class="btn-secondary" style="padding: 10px 15px; font-size: 14px;">
                            ✏️
                        </button>
                        <button onclick="deleteProduct(<?= $p['id'] ?>, '<?= addslashes($p['nama']) ?>')" class="btn-danger" style="padding: 10px 15px; font-size: 14px;">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($products)): ?>
        <div class="card p-3 text-center" style="margin-top: 40px;">
            <div style="font-size: 64px; margin-bottom: 20px;">📦</div>
            <h3 style="font-size: 24px; margin-bottom: 10px;">Belum ada produk</h3>
            <p style="color: #999; margin-bottom: 20px;">Klik tombol "Tambah Produk" untuk mulai menambahkan produk</p>
            <button onclick="openAddModal()" class="btn">+ Tambah Produk</button>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal Produk -->
    <div class="modal" id="product-modal">
        <div class="card" style="max-width: 500px; width: 90%; padding: 30px;">
            <div class="flex-between mb-2">
                <h2 id="modal-title" style="font-size: 24px;">Tambah Produk</h2>
                <button onclick="closeModal()" style="background: none; border: none; color: white; font-size: 32px; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            
            <form id="product-form" onsubmit="saveProduct(event)">
                <input type="hidden" id="product-id">
                
                <label>Nama Produk</label>
                <input type="text" id="product-nama" required>
                
                <label>Harga (Rp)</label>
                <input type="number" id="product-harga" min="0" required>
                
                <label>Deskripsi</label>
                <textarea id="product-deskripsi" rows="3" required></textarea>
                
                <label>Gambar Produk</label>
                <input type="text" id="product-gambar" placeholder="images/xiaomi-14.jpg">
                <small style="color: #999; font-size: 12px;">Masukkan path gambar (contoh: images/xiaomi-14.jpg)</small>
                
                <label>Kategori</label>
                <select id="product-kategori">
                    <option value="Smartphone">Smartphone</option>
                    <option value="Tablet">Tablet</option>
                    <option value="Wearable">Wearable</option>
                    <option value="Audio">Audio</option>
                    <option value="Smart Home">Smart Home</option>
                    <option value="Accessories">Accessories</option>
                </select>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn" style="flex: 1;">Simpan</button>
                    <button type="button" onclick="closeModal()" class="btn-secondary" style="flex: 1;">Batal</button>
                </div>
            </form>
        </div>
    </div>
    
    <div style="height: 50px;"></div>
    
    <script>
        const modal = document.getElementById('product-modal');
        
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Tambah Produk';
            document.getElementById('product-form').reset();
            document.getElementById('product-id').value = '';
            modal.style.display = 'flex';
        }
        
        function editProduct(product) {
            document.getElementById('modal-title').textContent = 'Edit Produk';
            document.getElementById('product-id').value = product.id;
            document.getElementById('product-nama').value = product.nama;
            document.getElementById('product-harga').value = product.harga;
            document.getElementById('product-deskripsi').value = product.deskripsi;
            document.getElementById('product-gambar').value = product.gambar || '';
            document.getElementById('product-kategori').value = product.kategori || 'Smartphone';
            modal.style.display = 'flex';
        }
        
        function closeModal() {
            modal.style.display = 'none';
        }
        
        async function saveProduct(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const id = document.getElementById('product-id').value;
            
            formData.append('action', id ? 'update' : 'add');
            if (id) formData.append('id', id);
            formData.append('nama', document.getElementById('product-nama').value);
            formData.append('harga', document.getElementById('product-harga').value);
            formData.append('deskripsi', document.getElementById('product-deskripsi').value);
            formData.append('gambar', document.getElementById('product-gambar').value);
            formData.append('kategori', document.getElementById('product-kategori').value);
            
            try {
                const response = await fetch('daftar_produk.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    alert(id ? 'Produk berhasil diupdate!' : 'Produk berhasil ditambahkan!');
                    location.reload();
                } else {
                    alert('Terjadi kesalahan!');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        async function deleteProduct(id, nama) {
            if (!confirm(`Hapus produk "${nama}"?`)) return;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            try {
                const response = await fetch('daftar_produk.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    alert('Produk berhasil dihapus!');
                    location.reload();
                } else {
                    alert('Terjadi kesalahan!');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>