# Xiaomi Store

## STEP 1: Setup Database

1. Buka phpMyAdmin atau MySQL client
2. Buat database baru:
   ```sql
   CREATE DATABASE xiaomi_store;
   ```
3. Import file SQL:
   - Pilih database 'xiaomi_store'
   - Import file 'xiaomi_store.sql'
   - Tunggu hingga selesai

## STEP 2: Upload File

1. Extract semua file ke folder web server:
   - **XAMPP**: `C:\xampp\htdocs\xiaomi_store\`
   - **WAMP**: `C:\wamp\www\xiaomi_store\`
   - **Linux**: `/var/www/html/xiaomi_store/`

2. Pastikan struktur folder seperti ini:
   ```
   xiaomi_store/
   ├── includes/
   ├── css/
   ├── images/
   ├── login.php
   ├── dashboard.php
   ├── daftar_produk.php
   ├── keranjang.php
   ├── checkout.php
   └── ...
   ```

## STEP 3: Konfigurasi Database

1. Buka file: `includes/koneksi_database.php`
2. Edit sesuai konfigurasi database Anda:
   ```php
   $db_host = 'localhost';      // Host database
   $db_user = 'root';           // Username MySQL
   $db_pass = '';               // Password MySQL
   $db_name = 'xiaomi_store';   // Nama database
   ```
3. Simpan file

## STEP 4: Set Permission (Linux/Mac)

```bash
chmod -R 755 xiaomi_store/
chmod -R 777 xiaomi_store/images/
```

## STEP 5: Akses Aplikasi

1. Buka browser
2. Akses: `http://localhost/xiaomi_store/`
3. Akan otomatis redirect ke login.php

## STEP 6: Login Pertama

- **Username**: `admin`
- **Password**: `password`
