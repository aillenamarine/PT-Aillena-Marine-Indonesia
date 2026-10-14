<?php
$pageTitle = 'Pusat Cadangan & Ekspor Data (Backup)';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();
$action = $_GET['action'] ?? 'view';

// 1. Direct Download SQLite DB File
if ($action === 'download_db') {
    $dbFile = DATA_PATH . '/aillena_marine.db';
    if (file_exists($dbFile)) {
        header('Content-Type: application/x-sqlite3');
        header('Content-Disposition: attachment; filename="aillena_marine_db_' . date('Y-m-d_His') . '.db"');
        header('Content-Length: ' . filesize($dbFile));
        readfile($dbFile);
        exit;
    } else {
        set_flash('danger', 'File database tidak ditemukan.');
        header('Location: backup.php');
        exit;
    }
}

// 2. Export Database as Structured JSON
if ($action === 'export_json') {
    $data = [
        'metadata' => [
            'app'        => APP_NAME,
            'version'    => APP_VERSION,
            'exported_at'=> date('c'),
            'generator'  => 'Aillena Marine Automated Backup Utility'
        ],
        'site_settings' => $pdo->query("SELECT * FROM site_settings")->fetchAll(),
        'gallery'       => $pdo->query("SELECT * FROM gallery")->fetchAll(),
        'inquiries'     => $pdo->query("SELECT * FROM inquiries")->fetchAll(),
        'admins'        => $pdo->query("SELECT id, username, full_name, role, created_at FROM admins")->fetchAll()
    ];

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="aillena_data_backup_' . date('Y-m-d_His') . '.json"');
    echo $json;
    exit;
}

// 3. Full ZIP Backup (Database + Uploaded Gallery Photos + JSON Dump)
if ($action === 'download_zip') {
    if (!class_exists('ZipArchive')) {
        set_flash('danger', 'Ekstensi PHP ZipArchive tidak aktif pada server ini. Silakan gunakan opsi Ekspor JSON atau Unduh File DB langsung.');
        header('Location: backup.php');
        exit;
    }

    $zipName = 'backup-aillena-marine-' . date('Y-m-d_His') . '.zip';
    $zipPath = BACKUP_PATH . '/' . $zipName;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        // Add Database file
        $dbFile = DATA_PATH . '/aillena_marine.db';
        if (file_exists($dbFile)) {
            $zip->addFile($dbFile, 'data/aillena_marine.db');
        }

        // Add JSON export dump inside the ZIP
        $dumpData = [
            'metadata'      => ['app' => APP_NAME, 'exported_at' => date('c')],
            'site_settings' => $pdo->query("SELECT * FROM site_settings")->fetchAll(),
            'gallery'       => $pdo->query("SELECT * FROM gallery")->fetchAll(),
            'inquiries'     => $pdo->query("SELECT * FROM inquiries")->fetchAll()
        ];
        $zip->addFromString('backup_data.json', json_encode($dumpData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Add Uploaded Gallery Files
        if (is_dir(GALLERY_UPLOADS_PATH)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(GALLERY_UPLOADS_PATH, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = 'uploads/gallery/' . substr($filePath, strlen(GALLERY_UPLOADS_PATH) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        $zip->close();

        // Stream ZIP to browser
        if (file_exists($zipPath)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipName . '"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);
            // Delete temp archive after download
            @unlink($zipPath);
            exit;
        }
    } else {
        set_flash('danger', 'Gagal membuat file arsip ZIP.');
        header('Location: backup.php');
        exit;
    }
}

// Calculate sizes
$dbFile = DATA_PATH . '/aillena_marine.db';
$dbSize = file_exists($dbFile) ? round(filesize($dbFile) / 1024, 2) : 0;

$uploadCount = 0;
$uploadSize = 0;
if (is_dir(GALLERY_UPLOADS_PATH)) {
    $files = scandir(GALLERY_UPLOADS_PATH);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            $uploadCount++;
            $uploadSize += filesize(GALLERY_UPLOADS_PATH . '/' . $f);
        }
    }
}
$uploadSizeMb = round($uploadSize / (1024 * 1024), 2);
?>

<div class="stats-overview-grid">
  <div class="stat-widget">
    <div class="stat-widget-info">
      <span>Database SQLite</span>
      <h3><?= $dbSize ?> KB</h3>
    </div>
    <div class="stat-widget-icon stat-icon-gold">
      <i class="fa-solid fa-database"></i>
    </div>
  </div>

  <div class="stat-widget">
    <div class="stat-widget-info">
      <span>File Upload Foto</span>
      <h3><?= $uploadCount ?> Berkas</h3>
    </div>
    <div class="stat-widget-icon stat-icon-cyan">
      <i class="fa-solid fa-images"></i>
    </div>
  </div>

  <div class="stat-widget">
    <div class="stat-widget-info">
      <span>Total Kapasitas Upload</span>
      <h3><?= $uploadSizeMb ?> MB</h3>
    </div>
    <div class="stat-widget-icon stat-icon-green">
      <i class="fa-solid fa-hard-drive"></i>
    </div>
  </div>
</div>

<!-- Backup Options Grid -->
<div class="form-grid-2">
  
  <!-- Option 1: Full ZIP Archive -->
  <div class="admin-card" style="border-color: rgba(212, 175, 55, 0.4);">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="fa-solid fa-file-zipper"></i> Full Backup Arsip (ZIP)</h3>
      <span class="status-pill status-done">Direkomendasikan</span>
    </div>
    <div class="admin-card-body">
      <p style="color: var(--text-light); font-size: 0.92rem; margin-bottom: 1.25rem;">
        Mengemas seluruh data penting website ke dalam satu berkas terkompresi:
      </p>
      <ul style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem; line-height: 1.8; list-style: disc; padding-left: 1.25rem;">
        <li>Basis data SQLite lengkap (riwayat formulir, data galeri, kredensial admin).</li>
        <li>Seluruh berkas foto dokumentasi proyek yang diunggah ke folder <code>uploads/gallery/</code>.</li>
        <li>Berkas <code>backup_data.json</code> terstruktur untuk cadangan independen.</li>
      </ul>
      <a href="backup.php?action=download_zip" class="btn btn-gold btn-block" style="width: 100%; justify-content: center; padding: 0.85rem;">
        <i class="fa-solid fa-download"></i> Unduh Full Backup ZIP Sekarang
      </a>
    </div>
  </div>

  <!-- Option 2: Individual DB & JSON Export -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="fa-solid fa-file-code"></i> Ekspor Spesifik (JSON / DB)</h3>
    </div>
    <div class="admin-card-body">
      <p style="color: var(--text-light); font-size: 0.92rem; margin-bottom: 1.25rem;">
        Pilihan ekspor cadangan ringan untuk arsip catatan teks atau migrasi cepat:
      </p>

      <div style="margin-bottom: 1.25rem; background: var(--admin-navy-primary); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--admin-navy-border);">
        <h4 style="font-size: 0.95rem; margin-bottom: 0.25rem;"><i class="fa-solid fa-code gold-text"></i> Ekspor Format JSON</h4>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.75rem;">Seluruh tabel diubah menjadi format JSON universal yang dapat dibaca di Excel atau database lain.</p>
        <a href="backup.php?action=export_json" class="btn btn-outline btn-sm">
          <i class="fa-solid fa-file-arrow-down"></i> Unduh JSON Backup
        </a>
      </div>

      <div style="background: var(--admin-navy-primary); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--admin-navy-border);">
        <h4 style="font-size: 0.95rem; margin-bottom: 0.25rem;"><i class="fa-solid fa-database gold-text"></i> Salin Berkas SQLite (.db)</h4>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.75rem;">Unduh berkas biner asli <code>aillena_marine.db</code> untuk dipindahkan ke server lain.</p>
        <a href="backup.php?action=download_db" class="btn btn-outline btn-sm">
          <i class="fa-solid fa-download"></i> Unduh File .db
        </a>
      </div>
    </div>
  </div>

</div>

<!-- Backup Guidelines Card -->
<div class="admin-card" style="background: rgba(11, 27, 50, 0.6);">
  <div class="admin-card-body">
    <h4 style="font-size: 1rem; color: var(--admin-gold); margin-bottom: 0.5rem;">
      <i class="fa-solid fa-circle-info"></i> Petunjuk Pemulihan Data (Restore):
    </h4>
    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.7; margin: 0;">
      Untuk memulihkan website dari berkas cadangan ZIP: cukup ekstrak berkas <code>aillena_marine.db</code> ke dalam folder <code>/data/</code> di hosting cPanel Anda, dan letakkan foto-foto di dalam folder <code>/uploads/gallery/</code>. Seluruh data, galeri, dan pesan akan langsung pulih seketika tanpa perlu proses konfigurasi SQL manual.
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
