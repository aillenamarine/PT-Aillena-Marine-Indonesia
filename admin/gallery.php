<?php
$pageTitle = 'Kelola Galeri & Foto Dokumentasi';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);
$editItem = null;

// Categories list
$categories = [
    'Pengecatan Bodi',
    'Overhaul Mesin',
    'Docking Rel',
    'Kelistrikan',
    'Sea Trial',
    'Workshop & Fasilitas'
];

// Handle DELETE
if ($action === 'delete' && $editId > 0) {
    if (verify_csrf($_GET['token'] ?? '')) {
        $stmt = $pdo->prepare("SELECT image_url FROM gallery WHERE id = :id");
        $stmt->execute([':id' => $editId]);
        $img = $stmt->fetchColumn();

        if ($img) {
            // If the image was uploaded in uploads/gallery/, remove the physical file
            if (strpos($img, 'uploads/gallery/') === 0) {
                $filePath = ROOT_PATH . '/' . $img;
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            $delStmt = $pdo->prepare("DELETE FROM gallery WHERE id = :id");
            $delStmt->execute([':id' => $editId]);
            set_flash('success', 'Foto galeri berhasil dihapus.');
        } else {
            set_flash('danger', 'Data foto tidak ditemukan.');
        }
    } else {
        set_flash('danger', 'Token keamanan CSRF tidak valid.');
    }
    header('Location: gallery.php');
    exit;
}

// Handle Form POST (Create or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Token keamanan tidak valid.');
        header('Location: gallery.php');
        exit;
    }

    $title       = sanitize($_POST['title'] ?? '');
    $category    = sanitize($_POST['category'] ?? '');
    $location    = sanitize($_POST['location'] ?? 'Workshop Memey Marine');
    $tags        = sanitize($_POST['tags'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $order       = (int)($_POST['display_order'] ?? 0);
    $itemId      = (int)($_POST['item_id'] ?? 0);

    if (empty($title) || empty($category)) {
        set_flash('danger', 'Judul foto dan kategori wajib diisi.');
    } else {
        $imageUrl = null;

        // Process File Upload if provided
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($fileExt, $allowedExtensions)) {
                set_flash('danger', 'Format gambar tidak didukung. Harap gunakan JPG, PNG, atau WEBP.');
                header('Location: gallery.php');
                exit;
            }

            // Max size 8MB
            if ($file['size'] > 8 * 1024 * 1024) {
                set_flash('danger', 'Ukuran gambar maksimal adalah 8 MB.');
                header('Location: gallery.php');
                exit;
            }

            // Generate clean unique filename
            $newFileName = 'dok-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $fileExt;
            $destPath = GALLERY_UPLOADS_PATH . '/' . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $imageUrl = 'uploads/gallery/' . $newFileName;
            } else {
                set_flash('danger', 'Gagal memindahkan file yang diunggah.');
                header('Location: gallery.php');
                exit;
            }
        }

        if ($itemId > 0) {
            // Update
            if ($imageUrl) {
                $upd = $pdo->prepare("
                    UPDATE gallery 
                    SET title = :title, category = :cat, location = :loc, tags = :tags, description = :desc, display_order = :ord, image_url = :img
                    WHERE id = :id
                ");
                $upd->execute([
                    ':title' => $title, ':cat' => $category, ':loc' => $location, ':tags' => $tags, 
                    ':desc' => $description, ':ord' => $order, ':img' => $imageUrl, ':id' => $itemId
                ]);
            } else {
                $upd = $pdo->prepare("
                    UPDATE gallery 
                    SET title = :title, category = :cat, location = :loc, tags = :tags, description = :desc, display_order = :ord
                    WHERE id = :id
                ");
                $upd->execute([
                    ':title' => $title, ':cat' => $category, ':loc' => $location, ':tags' => $tags, 
                    ':desc' => $description, ':ord' => $order, ':id' => $itemId
                ]);
            }
            set_flash('success', 'Informasi foto berhasil diperbarui.');
        } else {
            // Create
            if (!$imageUrl) {
                set_flash('danger', 'File gambar wajib dipilih untuk dokumentasi baru.');
                header('Location: gallery.php?action=create');
                exit;
            }
            $ins = $pdo->prepare("
                INSERT INTO gallery (title, category, location, tags, description, display_order, image_url)
                VALUES (:title, :cat, :loc, :tags, :desc, :ord, :img)
            ");
            $ins->execute([
                ':title' => $title, ':cat' => $category, ':loc' => $location, ':tags' => $tags, 
                ':desc' => $description, ':ord' => $order, ':img' => $imageUrl
            ]);
            set_flash('success', 'Foto dokumentasi proyek baru berhasil diunggah!');
        }

        header('Location: gallery.php');
        exit;
    }
}

// If edit action
if ($action === 'edit' && $editId > 0) {
    $editStmt = $pdo->prepare("SELECT * FROM gallery WHERE id = :id");
    $editStmt->execute([':id' => $editId]);
    $editItem = $editStmt->fetch();
}

// Fetch list of gallery items with category filter
$filterCat = sanitize($_GET['category'] ?? 'all');
if ($filterCat !== 'all' && !empty($filterCat)) {
    $listStmt = $pdo->prepare("SELECT * FROM gallery WHERE category = :cat ORDER BY display_order ASC, created_at DESC");
    $listStmt->execute([':cat' => $filterCat]);
} else {
    $listStmt = $pdo->query("SELECT * FROM gallery ORDER BY display_order ASC, created_at DESC");
}
$galleryItems = $listStmt->fetchAll();
?>

<!-- Upload / Edit Form Card -->
<?php if ($action === 'create' || $action === 'edit'): ?>
<div class="admin-card" style="border-color: rgba(212, 175, 55, 0.4);">
  <div class="admin-card-header">
    <h3 class="admin-card-title">
      <i class="fa-solid <?= $action === 'edit' ? 'fa-pen-to-square' : 'fa-cloud-arrow-up' ?>"></i>
      <?= $action === 'edit' ? 'Edit Dokumentasi Foto' : 'Unggah Foto Dokumentasi Baru' ?>
    </h3>
    <a href="gallery.php" class="btn btn-outline btn-sm">
      <i class="fa-solid fa-xmark"></i> Batal
    </a>
  </div>
  <div class="admin-card-body">
    <form method="POST" action="gallery.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="item_id" value="<?= $editItem['id'] ?? 0 ?>">

      <div class="form-grid-2">
        <div class="admin-form-group">
          <label for="title">Judul Foto Dokumentasi <span style="color:#F87171;">*</span></label>
          <input type="text" id="title" name="title" class="admin-input" placeholder="Contoh: Overhaul Lower Unit Mesin Yamaha 200HP" required value="<?= htmlspecialchars($editItem['title'] ?? '') ?>">
        </div>

        <div class="admin-form-group">
          <label for="category">Kategori Pengerjaan <span style="color:#F87171;">*</span></label>
          <select id="category" name="category" class="admin-select" required>
            <option value="" disabled <?= empty($editItem) ? 'selected' : '' ?>>Pilih Kategori</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>" <?= ($editItem['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-grid-2">
        <div class="admin-form-group">
          <label for="location">Lokasi Pekerjaan</label>
          <input type="text" id="location" name="location" class="admin-input" placeholder="Contoh: Workshop Memey Marine / Slipway Muara" value="<?= htmlspecialchars($editItem['location'] ?? 'Workshop Memey Marine') ?>">
        </div>

        <div class="admin-form-group">
          <label for="tags">Tag / Kata Kunci</label>
          <input type="text" id="tags" name="tags" class="admin-input" placeholder="Contoh: Yamaha, Overhaul, Gearcase" value="<?= htmlspecialchars($editItem['tags'] ?? '') ?>">
        </div>
      </div>

      <div class="admin-form-group">
        <label for="description">Deskripsi Teknis Pengerjaan</label>
        <textarea id="description" name="description" class="admin-textarea" rows="3" placeholder="Tuliskan keterangan teknis pengerjaan, standar SOP, atau spesifikasi suku cadang..."><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
      </div>

      <div class="form-grid-2">
        <div class="admin-form-group">
          <label for="photo">
            Pilih Berkas Gambar <?= $action === 'create' ? '<span style="color:#F87171;">*</span>' : '(Kosongkan jika tidak diganti)' ?>
          </label>
          <input type="file" id="photo" name="photo" class="admin-input" accept="image/jpeg,image/png,image/webp" <?= $action === 'create' ? 'required' : '' ?>>
          <small style="color: var(--text-muted); display:block; margin-top: 4px;">Format didukung: JPG, PNG, WEBP. Maksimal ukuran 8MB.</small>
        </div>

        <div class="admin-form-group">
          <label for="display_order">Urutan Tampil (Display Order)</label>
          <input type="number" id="display_order" name="display_order" class="admin-input" value="<?= (int)($editItem['display_order'] ?? 0) ?>" min="0">
          <small style="color: var(--text-muted); display:block; margin-top: 4px;">Angka lebih kecil tampil lebih awal (0 = paling awal).</small>
        </div>
      </div>

      <?php if (!empty($editItem['image_url'])): ?>
        <div style="margin-bottom: 1.25rem;">
          <label style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">Gambar Saat Ini:</label>
          <img src="../<?= htmlspecialchars($editItem['image_url']) ?>" style="max-height: 120px; border-radius: var(--radius-sm); border: 1px solid var(--admin-navy-border);">
        </div>
      <?php endif; ?>

      <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="submit" class="btn btn-gold">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Data Foto
        </button>
        <a href="gallery.php" class="btn btn-outline">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Gallery Filter & List Section -->
<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title"><i class="fa-solid fa-images"></i> Arsip Seluruh Galeri Dokumentasi (<?= count($galleryItems) ?>)</h3>
      <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Foto-foto ini langsung tersinkronisasi dengan halaman galeri website utama.</p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
      <!-- Category Filter Dropdown -->
      <form method="GET" action="gallery.php" style="display: flex; gap: 0.5rem; align-items: center;">
        <select name="category" class="admin-select" style="padding: 0.4rem 0.85rem; font-size: 0.82rem; width: auto;" onchange="this.form.submit()">
          <option value="all" <?= $filterCat === 'all' ? 'selected' : '' ?>>Semua Kategori</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c ?>" <?= $filterCat === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </form>

      <?php if ($action !== 'create'): ?>
        <a href="gallery.php?action=create" class="btn btn-gold btn-sm">
          <i class="fa-solid fa-cloud-arrow-up"></i> Tambah Foto Baru
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="admin-card-body">
    <?php if (empty($galleryItems)): ?>
      <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
        <i class="fa-solid fa-image" style="font-size: 3rem; margin-bottom: 0.75rem; display: block; opacity: 0.5;"></i>
        <p>Tidak ada foto dalam kategori ini.</p>
        <a href="gallery.php?action=create" class="btn btn-gold btn-sm" style="margin-top: 0.5rem;">
          <i class="fa-solid fa-plus"></i> Unggah Foto Pertama
        </a>
      </div>
    <?php else: ?>
      <div class="gallery-admin-grid">
        <?php foreach ($galleryItems as $item): ?>
          <div class="gallery-admin-card">
            <div class="gallery-admin-img-wrap">
              <img src="../<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
              <span class="gallery-admin-badge"><?= htmlspecialchars($item['category']) ?></span>
            </div>
            <div class="gallery-admin-body">
              <h4><?= htmlspecialchars($item['title']) ?></h4>
              <p><?= htmlspecialchars(mb_strimwidth($item['description'] ?? '', 0, 110, '...')) ?></p>
              
              <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                <div><i class="fa-solid fa-location-dot gold-text"></i> <?= htmlspecialchars($item['location'] ?: '-') ?></div>
                <?php if (!empty($item['tags'])): ?>
                  <div><i class="fa-solid fa-tags"></i> <?= htmlspecialchars($item['tags']) ?></div>
                <?php endif; ?>
              </div>

              <div class="gallery-admin-actions">
                <span style="font-size: 0.72rem; color: var(--text-muted);">
                  Urutan: <strong><?= $item['display_order'] ?></strong>
                </span>
                <div style="display: flex; gap: 0.4rem;">
                  <a href="gallery.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-outline btn-sm" title="Edit Data">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                  <a href="gallery.php?action=delete&id=<?= $item['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-danger btn-sm" title="Hapus Foto" onclick="return confirm('Apakah Anda yakin ingin menghapus foto dokumentasi ini?')">
                    <i class="fa-solid fa-trash"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
