<?php
$pageTitle = 'Pesan & Penawaran Masuk (Leads)';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();
$action = $_GET['action'] ?? 'list';
$inquiryId = (int)($_GET['id'] ?? 0);

// Status list
$statusList = ['Baru', 'Dihubungi', 'Sedang Diproses', 'Selesai', 'Dibatalkan'];

// Handle CSV Export
if ($action === 'export_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=inquiries-aillena-marine-' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    // Add BOM for Excel UTF-8 compatibility
    fputs($output, "\xEF\xBB\xBF");
    fputcsv($output, ['ID', 'Tanggal', 'Nama Klien', 'Instansi/Perusahaan', 'No. WhatsApp', 'Layanan', 'Detail Kapal/Mesin', 'Pesan Kendala', 'Status', 'Catatan Admin']);

    $stmt = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC");
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['created_at'],
            $row['client_name'],
            $row['client_org'],
            $row['client_phone'],
            $row['service_type'],
            $row['boat_details'],
            $row['message'],
            $row['status'],
            $row['admin_notes'] ?? ''
        ]);
    }
    fclose($output);
    exit;
}

// Handle Status / Notes Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inquiry'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Token keamanan CSRF tidak valid.');
        header('Location: inquiries.php');
        exit;
    }

    $id     = (int)$_POST['inquiry_id'];
    $status = sanitize($_POST['status'] ?? 'Baru');
    $notes  = sanitize($_POST['admin_notes'] ?? '');

    $upd = $pdo->prepare("UPDATE inquiries SET status = :status, admin_notes = :notes WHERE id = :id");
    $upd->execute([':status' => $status, ':notes' => $notes, ':id' => $id]);

    set_flash('success', 'Status dan catatan inquiry #' . $id . ' berhasil diperbarui.');
    header('Location: inquiries.php#inquiry-' . $id);
    exit;
}

// Handle Delete
if ($action === 'delete' && $inquiryId > 0) {
    if (verify_csrf($_GET['token'] ?? '')) {
        $del = $pdo->prepare("DELETE FROM inquiries WHERE id = :id");
        $del->execute([':id' => $inquiryId]);
        set_flash('success', 'Pesan inquiry #' . $inquiryId . ' berhasil dihapus.');
    } else {
        set_flash('danger', 'Token keamanan CSRF tidak valid.');
    }
    header('Location: inquiries.php');
    exit;
}

// Filter query
$statusFilter = sanitize($_GET['status'] ?? 'all');
if ($statusFilter !== 'all' && !empty($statusFilter)) {
    $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE status = :status ORDER BY created_at DESC");
    $stmt->execute([':status' => $statusFilter]);
} else {
    $stmt = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC");
}
$inquiries = $stmt->fetchAll();
?>

<!-- Header Actions & Filter -->
<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title"><i class="fa-solid fa-inbox"></i> Daftar Seluruh Permintaan Konsultasi (<?= count($inquiries) ?>)</h3>
      <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Pesan yang dikirim pengunjung melalui formulir website langsung tercatat di sini.</p>
    </div>

    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
      <form method="GET" action="inquiries.php" style="display: flex; gap: 0.5rem; align-items: center;">
        <select name="status" class="admin-select" style="padding: 0.4rem 0.85rem; font-size: 0.82rem; width: auto;" onchange="this.form.submit()">
          <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Semua Status</option>
          <?php foreach ($statusList as $s): ?>
            <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </form>

      <a href="inquiries.php?action=export_csv" class="btn btn-outline btn-sm">
        <i class="fa-solid fa-file-csv"></i> Unduh Laporan CSV
      </a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>No. Tiket</th>
          <th>Tanggal Masuk</th>
          <th>Klien & Instansi</th>
          <th>No. WhatsApp</th>
          <th>Layanan Diminta</th>
          <th>Status</th>
          <th style="text-align: right;">Aksi Cepat</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($inquiries)): ?>
          <tr>
            <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
              <i class="fa-solid fa-envelope-open" style="font-size: 2.5rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
              Tidak ada data pesan konsultasi yang sesuai filter.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($inquiries as $row): ?>
            <?php
              $cleanPhone = preg_replace('/[^0-9]/', '', $row['client_phone']);
              if (substr($cleanPhone, 0, 1) === '0') {
                  $waPhone = '62' . substr($cleanPhone, 1);
              } else {
                  $waPhone = $cleanPhone;
              }
              $replyWaText = "Halo Bapak/Ibu " . $row['client_name'] . " (" . $row['client_org'] . "), terima kasih telah menghubungi PT Aillena Marine Indonesia terkait layanan: " . $row['service_type'] . ". Ada yang bisa kami bantu diskusikan lebih lanjut?";
              $waReplyUrl = "https://wa.me/{$waPhone}?text=" . urlencode($replyWaText);
            ?>
            <tr id="inquiry-<?= $row['id'] ?>">
              <td style="font-weight: 700; color: var(--admin-gold);">#AMI-<?= $row['id'] ?></td>
              <td style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">
                <?= date('d M Y, H:i', strtotime($row['created_at'])) ?>
              </td>
              <td>
                <strong style="display: block; font-size: 0.92rem;"><?= htmlspecialchars($row['client_name']) ?></strong>
                <span style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($row['client_org'] ?: 'Perorangan / Mandiri') ?></span>
              </td>
              <td>
                <a href="<?= $waReplyUrl ?>" target="_blank" class="btn btn-whatsapp btn-sm" title="Buka Chat WhatsApp">
                  <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($row['client_phone']) ?>
                </a>
              </td>
              <td>
                <span style="font-weight: 600; color: #FFFFFF; font-size: 0.85rem; display: block;"><?= htmlspecialchars($row['service_type']) ?></span>
                <span style="font-size: 0.75rem; color: var(--text-muted);">Kapal: <?= htmlspecialchars($row['boat_details'] ?: '-') ?></span>
              </td>
              <td>
                <?php
                  $statusClass = 'status-new';
                  if ($row['status'] === 'Dihubungi' || $row['status'] === 'Sedang Diproses') $statusClass = 'status-progress';
                  if ($row['status'] === 'Selesai') $statusClass = 'status-done';
                ?>
                <span class="status-pill <?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></span>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <button type="button" class="btn btn-outline btn-sm" onclick="toggleDetails(<?= $row['id'] ?>)">
                  <i class="fa-solid fa-angle-down"></i> Detail
                </button>
                <a href="inquiries.php?action=delete&id=<?= $row['id'] ?>&token=<?= csrf_token() ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus pesan inquiry ini dari database?')" title="Hapus">
                  <i class="fa-solid fa-trash"></i>
                </a>
              </td>
            </tr>

            <!-- Expanded Details Row (Hidden by default) -->
            <tr id="details-<?= $row['id'] ?>" style="display: none; background: rgba(7, 18, 33, 0.4);">
              <td colspan="7" style="padding: 1.5rem 2rem;">
                <div style="background: var(--admin-card-bg); border: 1px solid var(--admin-card-border); border-radius: var(--radius-md); padding: 1.5rem;">
                  <h4 style="font-size: 1rem; color: var(--admin-gold); margin-bottom: 0.75rem;">
                    <i class="fa-solid fa-clipboard-list"></i> Rincian Lengkap Permintaan Konsultasi
                  </h4>
                  <div style="margin-bottom: 1.25rem; font-size: 0.9rem; line-height: 1.6; background: rgba(0,0,0,0.2); padding: 1rem; border-radius: var(--radius-sm);">
                    <?= nl2br(htmlspecialchars($row['message'])) ?>
                  </div>

                  <!-- Status & Notes Update Form -->
                  <form method="POST" action="inquiries.php" style="display: grid; grid-template-columns: 200px 1fr auto; gap: 1rem; align-items: end;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="inquiry_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="update_inquiry" value="1">

                    <div class="admin-form-group" style="margin: 0;">
                      <label style="font-size: 0.8rem;">Status Penanganan</label>
                      <select name="status" class="admin-select" style="padding: 0.6rem;">
                        <?php foreach ($statusList as $st): ?>
                          <option value="<?= $st ?>" <?= $row['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="admin-form-group" style="margin: 0;">
                      <label style="font-size: 0.8rem;">Catatan Internal Tim / Estimasi RAB</label>
                      <input type="text" name="admin_notes" class="admin-input" placeholder="Tuliskan catatan progres atau estimasi..." value="<?= htmlspecialchars($row['admin_notes'] ?? '') ?>">
                    </div>

                    <button type="submit" class="btn btn-gold btn-sm" style="height: 42px;">
                      <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  function toggleDetails(id) {
    const row = document.getElementById('details-' + id);
    if (row.style.display === 'none' || row.style.display === '') {
      row.style.display = 'table-row';
    } else {
      row.style.display = 'none';
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
