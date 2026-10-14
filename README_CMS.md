# Panduan CMS & Full Stack - PT Aillena Marine Indonesia

Sistem ini telah dilengkapi dengan **Full Stack Content Management System (CMS)** berbasis **PHP & SQLite** yang ringan, aman, dan tanpa perlu instalasi database server terpisah.

---

## 1. Akses Dashboard Admin CMS

* **URL Panel Admin:** `http://domain-anda.com/admin/` (atau `admin/login.php`)
* **Kredensial Default:**
  * **Username:** `admin`
  * **Password:** `AdminMarine2026!`
  *(Segera ganti kata sandi setelah masuk pertama kali melalui menu **Pengaturan Website**).*

---

## 2. Fitur-Fitur Utama CMS

### A. Manajemen Galeri & Dokumentasi Proyek (`/admin/gallery.php`)
* **Upload Foto Baru:** Unggah dokumentasi foto pekerjaan (overhaul mesin tempel, pelapisan antifouling, docking rel, kelistrikan, sea trial).
* **Format Didukung:** JPG, PNG, WEBP (maksimal 8 MB per foto).
* **Metadata Lengkap:** Judul pengerjaan, kategori, lokasi workshop/muara, tag kata kunci, dan deskripsi teknis SOP.
* **Tersinkronisasi Otomatis:** Foto baru yang diunggah langsung tampil di galeri website utama (`gallery.html` & `index.html`).
* **Edit & Hapus:** Dilengkapi tombol sunting data dan penghapusan aman (file fisik gambar di folder `uploads/gallery/` ikut terhapus).

### B. Pusat Cadangan Data (Backup & Restore) (`/admin/backup.php`)
* **Unduh Full Backup ZIP (Satu Klik):**
  * Mengemas database lengkap (`data/aillena_marine.db`).
  * Mengemas seluruh arsip foto yang pernah diunggah (`uploads/gallery/`).
  * Mengemas dump data terstruktur `backup_data.json`.
* **Ekspor JSON Universal:** Ekspor seluruh isi tabel (inquiry, galeri, pengaturan) dalam format JSON.
* **Unduh File Database Asli (.db):** Untuk kemudahan pemindahan server kapan saja.
* **Prosedur Restore (Pemulihan):** Jika pindah hosting, cukup letakkan file `aillena_marine.db` ke folder `data/` dan file gambar ke `uploads/gallery/`. Data langsung aktif kembali seketika!

### C. Manajemen Pesan & Penawaran Masuk (`/admin/inquiries.php`)
* **Database Leads Otomatis:** Setiap kali pengunjung mengirim formulir konsultasi, data otomatis tersimpan rapi dengan No. Tiket `#AMI-...`.
* **Respon WhatsApp Instan:** Tombol satu klik "Chat WhatsApp" dengan teks balasan otomatis menyapa nama dan instansi klien.
* **Status Progres:** Update status inquiry (`Baru` -> `Dihubungi` -> `Sedang Diproses` -> `Selesai`).
* **Catatan Internal:** Tambahkan catatan estimasi RAB atau kebutuhan teknis armada klien.
* **Ekspor CSV:** Unduh rekap seluruh pesan masuk ke format file Excel/CSV.

### D. Pengaturan Profil Perusahaan (`/admin/settings.php`)
* Ubah nomor WhatsApp tujuan konsultasi, nomor telepon, email, alamat workshop, jam buka operasional, dan teks darurat 24 jam tanpa perlu membuka kode program.
* Penggantian username & kata sandi admin dengan enkripsi `Bcrypt`.

---

## 3. Struktur Direktori Full Stack

```text
├── admin/                     # Dashboard & Manajemen Konten
│   ├── css/admin.css          # Desain antarmuka dashboard executive navy-gold
│   ├── includes/              # Layout template & autentikasi
│   ├── index.php              # Ringkasan statistik & aksi cepat
│   ├── gallery.php            # Upload & kelola galeri foto
│   ├── inquiries.php          # Database pesan konsultasi & leads
│   ├── settings.php           # Pengaturan kontak & ganti password
│   ├── backup.php             # Backup ZIP & ekspor JSON
│   ├── login.php              # Halaman masuk aman (CSRF protected)
│   └── logout.php             # Sesi keluar
├── api/                       # REST API
│   ├── submit_inquiry.php     # Endpoint penerima formulir klien
│   ├── get_gallery.php        # Endpoint pembaca galeri dinamis
│   └── get_settings.php       # Endpoint pembaca info kontak
├── config/
│   ├── config.php             # Konfigurasi aplikasi & session
│   └── database.php           # Koneksi PDO SQLite & migrasi tabel otomatis
├── data/
│   ├── .htaccess              # Keamanan: blokir akses publik ke file database
│   └── aillena_marine.db      # File database SQLite (dibuat otomatis)
├── uploads/
│   ├── gallery/               # Folder penyimpanan foto hasil upload CMS
│   └── backups/               # Folder arsip sementara
├── index.html                 # Halaman Beranda Utama
├── about.html                 # Halaman Profil Perusahaan
├── services.html              # Halaman Katalog Layanan
├── workflow.html              # Halaman Alur Kerja (SOP 5 Tahap)
├── gallery.html               # Halaman Galeri Dokumentasi Proyek
├── styles.css                 # Stylesheet & Animasi Elegan Maritim
└── script.js                  # Logika UI & Integrasi API CMS
```

---

## 4. Panduan Publikasi ke Hosting (cPanel / Shared Hosting)

1. Kompres seluruh file di folder proyek ini menjadi satu file ZIP.
2. Masuk ke **cPanel** hosting Anda (Niagahoster, DomaiNesia, IDCloudHost, Hostinger, dll).
3. Buka **File Manager** -> masuk ke folder `public_html`.
4. Unggah file ZIP dan lakukan **Extract**.
5. Pastikan hak akses folder `data/` dan `uploads/` dapat ditulis (*permissions 755* atau *775*).
6. Buka `https://domain-anda.com/admin/` untuk mulai mengelola website!
