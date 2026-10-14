<?php
require_once __DIR__ . '/auth.php';
$currentPage = basename($_SERVER['PHP_SELF']);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Dashboard CMS') ?> | PT Aillena Marine Indonesia</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Admin Stylesheet -->
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>

  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
      <div class="admin-logo-mark">
        <i class="fa-solid fa-anchor"></i>
      </div>
      <div class="admin-brand-text">
        <h3>Aillena Marine</h3>
        <span>CMS DASHBOARD</span>
      </div>
    </div>

    <nav class="admin-nav">
      <div class="admin-nav-section-title">Menu Utama</div>
      <ul class="admin-nav-list">
        <li>
          <a href="index.php" class="admin-nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Ringkasan Dashboard</span>
          </a>
        </li>
        <li>
          <a href="gallery.php" class="admin-nav-link <?= $currentPage === 'gallery.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-images"></i>
            <span>Kelola Galeri & Foto</span>
          </a>
        </li>
        <li>
          <a href="inquiries.php" class="admin-nav-link <?= $currentPage === 'inquiries.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-inbox"></i>
            <span>Pesan Masuk (Leads)</span>
            <?php if ($newInquiriesCount > 0): ?>
              <span class="nav-badge"><?= $newInquiriesCount ?></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>

      <div class="admin-nav-section-title">Sistem & Pemeliharaan</div>
      <ul class="admin-nav-list">
        <li>
          <a href="backup.php" class="admin-nav-link <?= $currentPage === 'backup.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-cloud-arrow-down"></i>
            <span>Backup & Export Data</span>
          </a>
        </li>
        <li>
          <a href="settings.php" class="admin-nav-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-sliders"></i>
            <span>Pengaturan Website</span>
          </a>
        </li>
        <li>
          <a href="../index.html" target="_blank" class="admin-nav-link">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            <span>Lihat Website Utama</span>
          </a>
        </li>
        <li>
          <a href="logout.php" class="admin-nav-link" style="color: #F87171;">
            <i class="fa-solid fa-right-from-bracket" style="color: #F87171;"></i>
            <span>Keluar (Logout)</span>
          </a>
        </li>
      </ul>
    </nav>

    <div class="admin-sidebar-footer">
      <div class="admin-user-pill">
        <div class="admin-avatar">
          <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="admin-user-meta">
          <strong><?= htmlspecialchars($adminUser['name'] ?? 'Admin') ?></strong>
          <span>@<?= htmlspecialchars($adminUser['username'] ?? 'admin') ?></span>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Content Wrapper -->
  <div class="admin-main">
    <header class="admin-topbar">
      <div class="admin-topbar-left">
        <button class="mobile-nav-toggle" id="mobileNavToggle" aria-label="Toggle Navigation">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h2 class="admin-page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h2>
      </div>

      <div class="admin-topbar-right">
        <a href="backup.php" class="btn btn-outline btn-sm">
          <i class="fa-solid fa-floppy-disk"></i> Backup Cepat
        </a>
        <a href="gallery.php?action=create" class="btn btn-gold btn-sm">
          <i class="fa-solid fa-plus"></i> Upload Foto
        </a>
      </div>
    </header>

    <main class="admin-content">
      <?php if ($flash): ?>
        <div class="admin-alert admin-alert-<?= $flash['type'] === 'danger' ? 'danger' : ($flash['type'] === 'info' ? 'info' : 'success') ?>">
          <i class="fa-solid <?= $flash['type'] === 'danger' ? 'fa-triangle-exclamation' : ($flash['type'] === 'info' ? 'fa-circle-info' : 'fa-circle-check') ?>"></i>
          <span><?= htmlspecialchars($flash['message']) ?></span>
        </div>
      <?php endif; ?>
