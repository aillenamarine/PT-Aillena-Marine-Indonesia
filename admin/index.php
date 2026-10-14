<?php
$pageTitle = 'Ringkasan Dashboard';
require_once __DIR__ . '/includes/header.php';

try {
    $pdo = get_db_connection();

    // Get statistics
    $totalGallery = (int)$pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
    $totalInquiries = (int)$pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
    $newInquiries = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Baru'")->fetchColumn();

    // Recent 5 Inquiries
    $recentInquiriesStmt = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 5");
    $recentInquiries = $recentInquiriesStmt->fetchAll();

    // Recent 4 Gallery items
    $recentGalleryStmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 4");
    $recentGallery = $recentGalleryStmt->fetchAll();

    // Database file size
    $dbFile = DATA_PATH . '/aillena_marine.db';
    $dbSizeKb = file_exists($dbFile) ? round(filesize($dbFile) / 1024, 1) : 0;

} catch (Exception $e) {
    error_log('Dashboard error: ' . $e->getMessage());
    $totalGallery = 0;
    $totalInquiries = 0;
    $newInquiries = 0;
    $recentInquiries = [];
    $recentGallery = [];
    $dbSizeKb = 0;
}
?>

<!-- Statistics Overview -->
<div class="stats-overview-grid">
  <div class="stat-widget">
    <div class="stat-widget-info">
      <span>Foto Galeri Aktif</span>
      <h3><?= $totalGallery ?></h3>
    </div>
    <div class="stat-widget-icon stat-icon-gold">
      <i class="fa-solid fa-images"></i>
    </div>
  </div>

  <div class="stat-widget">
    <div class="stat-widget-info">
      <span>Pesan Masuk (Leads)</span>
      <h3><?= $totalInquiries ?></h3>
    </div>
    <div class="stat-widget-icon stat-icon-cyan">
      <i class="fa-solid fa-inbox"></i>
    </div>
  </div>

  <div class="stat-widget">
    <div class="stat-widget-info">
      <span>Perlu Dihubungi</span>
      <h3><?= $newInquiries ?></h3>
    </div>
    <div class="stat-widget-icon stat-icon-green">
      <i class="fa-solid fa-bell"></i>
    </div>
  </div>

  <div class="stat-widget">
    <div class="stat-widget-info">
      <span>Ukuran Database</span>
      <h3><?= $dbSizeKb ?> KB</h3>
    </div>
    <div class="stat-widget-icon stat-icon-gold">
      <i class="fa-solid fa-database"></i>
    </div>
  </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="admin-card" style="background: linear-gradient(135deg, rgba(17, 40, 72, 0.9), rgba(11, 27, 50, 0.9)); border-color: rgba(212, 175, 55, 0.3);">
  <div class="admin-card-body" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h3 style="font-size: 1.15rem; margin-bottom: 0.25rem;"><i class="fa-solid fa-bolt gold-text"></i> Tindakan Cepat CMS</h3>
      <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Kelola konten dokumentasi, amankan backup data web, atau respon penawaran klien secara instan.</p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
      <a href="gallery.php?action=create" class="btn btn-gold btn-sm">
        <i class="fa-solid fa-cloud-arrow-up"></i> Upload Foto Baru
      </a>
      <a href="backup.php?action=download_zip" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-file-zipper"></i> Unduh Full Backup ZIP
      </a>
      <a href="inquiries.php" class="btn btn-outline btn-sm">
        <i class="fa-solid fa-envelope-open-text"></i> Cek Semua Pesan
      </a>
    </div>
  </div>
</div>

<!-- Recent Inquiries Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title">
      <i class="fa-solid fa-envelope"></i> Permintaan Konsultasi & Penawaran Terbaru
    </h3>
    <a href="inquiries.php" class="btn btn-outline btn-sm">
      Lihat Semua (<?= $totalInquiries ?>) <i class="fa-solid fa-chevron-right"></i>
    </a>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Nama Klien</th>
          <th>Instansi / Usaha</th>
          <th>No. WhatsApp</th>
          <th>Layanan Diminta</th>
          <th>Status</th>
          <th style="text-align: right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recentInquiries)): ?>
          <tr>
            <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
              <i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
              Belum ada pesan konsultasi yang masuk.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($recentInquiries as $inq): ?>
            <tr>
              <td style="font-size: 0.8rem; color: var(--text-muted);">
                <?= date('d M Y, H:i', strtotime($inq['created_at'])) ?>
              </td>
              <td><strong><?= htmlspecialchars($inq['client_name']) ?></strong></td>
              <td><?= htmlspecialchars($inq['client_org'] ?: '-') ?></td>
              <td>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $inq['client_phone']) ?>" target="_blank" style="color: #34D399; font-weight: 600;">
                  <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($inq['client_phone']) ?>
                </a>
              </td>
              <td><span style="font-size: 0.82rem;"><?= htmlspecialchars($inq['service_type']) ?></span></td>
              <td>
                <?php
                  $statusClass = 'status-new';
                  if ($inq['status'] === 'Dihubungi' || $inq['status'] === 'Sedang Diproses') $statusClass = 'status-progress';
                  if ($inq['status'] === 'Selesai') $statusClass = 'status-done';
                ?>
                <span class="status-pill <?= $statusClass ?>"><?= htmlspecialchars($inq['status']) ?></span>
              </td>
              <td style="text-align: right;">
                <a href="inquiries.php#inquiry-<?= $inq['id'] ?>" class="btn btn-outline btn-sm" title="Rincian Pesan">
                  <i class="fa-solid fa-eye"></i> Detail
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Recent Gallery Showcase -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title">
      <i class="fa-solid fa-camera-retro"></i> Dokumentasi Foto Proyek Terbaru
    </h3>
    <a href="gallery.php" class="btn btn-outline btn-sm">
      Kelola Galeri <i class="fa-solid fa-chevron-right"></i>
    </a>
  </div>
  <div class="admin-card-body">
    <?php if (empty($recentGallery)): ?>
      <p style="color: var(--text-muted); text-align: center; padding: 2rem;">Belum ada foto yang diunggah.</p>
    <?php else: ?>
      <div class="gallery-admin-grid">
        <?php foreach ($recentGallery as $item): ?>
          <div class="gallery-admin-card">
            <div class="gallery-admin-img-wrap">
              <img src="../<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
              <span class="gallery-admin-badge"><?= htmlspecialchars($item['category']) ?></span>
            </div>
            <div class="gallery-admin-body">
              <h4><?= htmlspecialchars($item['title']) ?></h4>
              <p><?= htmlspecialchars(mb_strimwidth($item['description'] ?? '', 0, 95, '...')) ?></p>
              <div class="gallery-admin-actions">
                <span style="font-size: 0.72rem; color: var(--text-muted);">
                  <i class="fa-solid fa-calendar-days"></i> <?= date('d M Y', strtotime($item['created_at'])) ?>
                </span>
                <a href="gallery.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-outline btn-sm">
                  <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
