<?php
$pageTitle = 'Pengaturan Informasi Website & Keamanan';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();

// Handle Settings Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Token keamanan CSRF tidak valid.');
        header('Location: settings.php');
        exit;
    }

    // A. Update General Company Settings
    if (isset($_POST['save_settings'])) {
        $fields = [
            'company_name'       => sanitize($_POST['company_name'] ?? ''),
            'company_tagline'    => sanitize($_POST['company_tagline'] ?? ''),
            'company_phone'      => sanitize($_POST['company_phone'] ?? ''),
            'company_whatsapp'   => preg_replace('/[^0-9]/', '', $_POST['company_whatsapp'] ?? ''),
            'company_email'      => sanitize($_POST['company_email'] ?? ''),
            'company_address'    => sanitize($_POST['company_address'] ?? ''),
            'operating_hours'    => sanitize($_POST['operating_hours'] ?? ''),
            'emergency_notice'   => sanitize($_POST['emergency_notice'] ?? ''),
            'inaproc_lkpp_url'   => sanitize($_POST['inaproc_lkpp_url'] ?? ''),
            'maps_embed_url'     => sanitize($_POST['maps_embed_url'] ?? '')
        ];

        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = :val, updated_at = CURRENT_TIMESTAMP WHERE setting_key = :key");
        foreach ($fields as $key => $val) {
            $stmt->execute([':val' => $val, ':key' => $key]);
        }

        set_flash('success', 'Informasi profil perusahaan berhasil disimpan.');
        header('Location: settings.php');
        exit;
    }

    // B. Update Admin Credentials
    if (isset($_POST['change_password'])) {
        $currentPass = $_POST['current_password'] ?? '';
        $newUsername = trim($_POST['new_username'] ?? '');
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        $adminId = $_SESSION['admin_user']['id'] ?? 1;
        $adminStmt = $pdo->prepare("SELECT * FROM admins WHERE id = :id");
        $adminStmt->execute([':id' => $adminId]);
        $admin = $adminStmt->fetch();

        if (!$admin || !password_verify($currentPass, $admin['password_hash'])) {
            set_flash('danger', 'Kata sandi saat ini yang Anda masukkan salah.');
        } elseif (strlen($newPass) < 8) {
            set_flash('danger', 'Kata sandi baru minimal harus 8 karakter.');
        } elseif ($newPass !== $confirmPass) {
            set_flash('danger', 'Konfirmasi kata sandi baru tidak cocok.');
        } else {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $updAdmin = $pdo->prepare("UPDATE admins SET username = :usr, password_hash = :pwd WHERE id = :id");
            $updAdmin->execute([':usr' => $newUsername ?: $admin['username'], ':pwd' => $newHash, ':id' => $adminId]);

            $_SESSION['admin_user']['username'] = $newUsername ?: $admin['username'];
            set_flash('success', 'Username & kata sandi admin berhasil diperbarui!');
        }

        header('Location: settings.php');
        exit;
    }
}

// Fetch current settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$currentSettings = [];
while ($row = $stmt->fetch()) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="form-grid-2">
  <!-- General Site Settings -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="fa-solid fa-sliders"></i> Informasi Perusahaan & Kontak</h3>
    </div>
    <div class="admin-card-body">
      <form method="POST" action="settings.php">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="save_settings" value="1">

        <div class="admin-form-group">
          <label>Nama Perusahaan</label>
          <input type="text" name="company_name" class="admin-input" value="<?= htmlspecialchars($currentSettings['company_name'] ?? '') ?>" required>
        </div>

        <div class="admin-form-group">
          <label>Tagline Resmi</label>
          <input type="text" name="company_tagline" class="admin-input" value="<?= htmlspecialchars($currentSettings['company_tagline'] ?? '') ?>">
        </div>

        <div class="form-grid-2">
          <div class="admin-form-group">
            <label>No. Telepon Kantor</label>
            <input type="text" name="company_phone" class="admin-input" value="<?= htmlspecialchars($currentSettings['company_phone'] ?? '') ?>">
          </div>
          <div class="admin-form-group">
            <label>No. WhatsApp Penerima Pesan (Format: 628...)</label>
            <input type="text" name="company_whatsapp" class="admin-input" value="<?= htmlspecialchars($currentSettings['company_whatsapp'] ?? '') ?>" required>
          </div>
        </div>

        <div class="admin-form-group">
          <label>Email Resmi</label>
          <input type="email" name="company_email" class="admin-input" value="<?= htmlspecialchars($currentSettings['company_email'] ?? '') ?>" required>
        </div>

        <div class="admin-form-group">
          <label>Alamat Kantor & Workshop</label>
          <textarea name="company_address" class="admin-textarea" rows="2"><?= htmlspecialchars($currentSettings['company_address'] ?? '') ?></textarea>
        </div>

        <div class="form-grid-2">
          <div class="admin-form-group">
            <label>Jam Operasional</label>
            <input type="text" name="operating_hours" class="admin-input" value="<?= htmlspecialchars($currentSettings['operating_hours'] ?? '') ?>">
          </div>
          <div class="admin-form-group">
            <label>Pemberitahuan Layanan Darurat</label>
            <input type="text" name="emergency_notice" class="admin-input" value="<?= htmlspecialchars($currentSettings['emergency_notice'] ?? '') ?>">
          </div>
        </div>

        <div class="admin-form-group">
          <label>Tautan Profil E-Katalog LKPP (INAPROC)</label>
          <input type="url" name="inaproc_lkpp_url" class="admin-input" value="<?= htmlspecialchars($currentSettings['inaproc_lkpp_url'] ?? '') ?>">
        </div>

        <div style="margin-top: 1.5rem;">
          <button type="submit" class="btn btn-gold">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Security & Password Change -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="fa-solid fa-shield-halved"></i> Keamanan & Akun Admin</h3>
    </div>
    <div class="admin-card-body">
      <form method="POST" action="settings.php">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="change_password" value="1">

        <div class="admin-form-group">
          <label>Username Admin</label>
          <input type="text" name="new_username" class="admin-input" value="<?= htmlspecialchars($_SESSION['admin_user']['username'] ?? 'admin') ?>" required>
        </div>

        <div class="admin-form-group">
          <label>Kata Sandi Saat Ini (Current Password) <span style="color:#F87171;">*</span></label>
          <input type="password" name="current_password" class="admin-input" placeholder="Masukkan kata sandi lama" required>
        </div>

        <div class="admin-form-group">
          <label>Kata Sandi Baru (Minimal 8 karakter) <span style="color:#F87171;">*</span></label>
          <input type="password" name="new_password" class="admin-input" placeholder="••••••••" required minlength="8">
        </div>

        <div class="admin-form-group">
          <label>Ulangi Kata Sandi Baru <span style="color:#F87171;">*</span></label>
          <input type="password" name="confirm_password" class="admin-input" placeholder="••••••••" required minlength="8">
        </div>

        <div style="margin-top: 1.5rem;">
          <button type="submit" class="btn btn-outline" style="border-color: var(--admin-gold); color: #FFFFFF;">
            <i class="fa-solid fa-key"></i> Perbarui Kata Sandi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
