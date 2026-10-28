# 📚 Rekap Nilai Mahasiswa — UTS Pemrograman Web

Aplikasi web **Rekap Nilai Mahasiswa** untuk mengelola data nilai mahasiswa dengan fitur CRUD, searching, sorting, dan paging. Dibangun menggunakan **PHP**, **MySQL**, dan template **AdminLTE 4**.

---

## 🎯 Fitur UTS

### 1. 📷 Foto di Kolom Nama Lengkap
- Setiap mahasiswa dapat memiliki **foto profil** yang ditampilkan di samping nama
- Jika belum upload foto, akan muncul **avatar placeholder** dengan inisial nama
- Upload foto mendukung format: JPG, JPEG, PNG, GIF, WEBP

### 2. 🔍 Searching (WHERE)
- **Berdasarkan Nama** — Input text untuk mencari nama mahasiswa (`WHERE nama_lengkap LIKE '%keyword%'`)
- **Berdasarkan Predikat** — Dropdown pilihan untuk filter berdasarkan predikat:
  - ✅ Lulus (A/B/C)
  - ❌ Tidak Lulus (D/E)
  - Detail: Sangat Memuaskan, Memuaskan, Cukup Memuaskan, Kurang, Sangat Kurang

### 3. 🔃 Sorting (ORDER BY)
- **Nama Lengkap** — Klik header kolom untuk toggle **Ascending (A→Z)** / **Descending (Z→A)**
- **Nilai Akhir** — Klik header kolom untuk toggle **Rendah→Tinggi** / **Tinggi→Rendah**
- Jika nama sama, otomatis dibedakan berdasarkan nilai akhir

### 4. 📄 Paging (LIMIT OFFSET)
- Pilihan jumlah data per halaman: **5, 10, 15**
- Navigasi halaman lengkap: First, Previous, Nomor Halaman, Next, Last
- Informasi "Menampilkan X sampai Y dari Z data"

### 5. ✏️ CRUD Lengkap
- **Tambah** data mahasiswa (dengan upload foto)
- **Edit** data mahasiswa (dengan ganti foto)
- **Hapus** data mahasiswa
- Konversi nilai otomatis (Nilai Akhir → Nilai Huruf → Predikat)

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Keterangan |
|-----------|------------|
| **PHP** | Backend server-side |
| **MySQL** | Database |
| **AdminLTE 4** | Template admin dashboard |
| **Bootstrap 5** | CSS framework |
| **Bootstrap Icons** | Icon library |

---

## 📊 Konversi Nilai

| Nilai Akhir | Huruf | Predikat |
|:-----------:|:-----:|----------|
| 80.51 – 100 | A | Sangat Memuaskan |
| 65.51 – 80.50 | B | Memuaskan |
| 50.51 – 65.50 | C | Cukup Memuaskan |
| 34.51 – 50.50 | D | Kurang |
| 0 – 34.50 | E | Sangat Kurang |

---

## 🚀 Cara Menjalankan

### Prasyarat
- **XAMPP / Laragon** (PHP + MySQL)
- Web browser

### Langkah-langkah

1. **Clone repository**
   ```bash
   git clone https://github.com/muhammadzacky1303/Praktikum-Pemrograman-Web.git
   ```

2. **Pindahkan** folder ke direktori web server
   - XAMPP: `C:\xampp\htdocs\`
   - Laragon: `C:\laragon\www\`

3. **Buat database** MySQL dengan nama `db5d`
   - Buka phpMyAdmin → Buat database baru bernama `db5d`
   - Tabel `mahasiswa` akan dibuat otomatis oleh aplikasi, atau buat manual:
   ```sql
   CREATE DATABASE IF NOT EXISTS db5d;
   USE db5d;

   CREATE TABLE mahasiswa (
       nim VARCHAR(20) PRIMARY KEY,
       nama_lengkap VARCHAR(100) NOT NULL,
       nilai_akhir DECIMAL(5,2) NOT NULL,
       nilai_huruf CHAR(1),
       predikat VARCHAR(30),
       foto VARCHAR(255) DEFAULT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

4. **Buka browser** dan akses:
   ```
   http://localhost/Praktikum-Pemrograman-Web/rekap_nilai.php
   ```

---

## 📁 Struktur File

```
Praktikum-Pemrograman-Web/
├── css/
│   └── css-admin.css          # Stylesheet AdminLTE
├── js/
│   └── js-admin.js            # JavaScript AdminLTE
├── uploads/
│   └── foto/                  # Folder upload foto mahasiswa
├── rekap_nilai.php            # ⭐ Halaman utama (UTS)
├── koneksi.php                # Koneksi database
├── Konversi_nilai.php         # Konversi nilai
├── form_nilai.html            # Form input nilai
├── form_guest.html            # Form guest book
├── insernilai.php             # Insert nilai
├── bacadata_nilai.php         # Baca data nilai
├── edit_nilai.php             # Edit nilai
├── hapus_nilai.php            # Hapus nilai
├── index-admin.html           # Template AdminLTE
└── README.md                  # Dokumentasi
```

---

## 👤 Author

**Muhammad Zacky**  
Praktikum Pemrograman Web — Prodi Teknik Informatika

---

> 📌 **Note:** Aplikasi ini membutuhkan server PHP dan MySQL untuk berjalan. Tidak dapat dijalankan langsung di GitHub Pages.
