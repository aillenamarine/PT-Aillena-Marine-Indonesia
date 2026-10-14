<?php
/**
 * Admin Login Page
 */

require_once dirname(__DIR__) . '/config/database.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token    = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Validasi sesi keamanan (CSRF) gagal. Silakan coba kembali.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Regenerate session ID for security against session fixation
                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = [
                    'id'       => $admin['id'],
                    'username' => $admin['username'],
                    'name'     => $admin['full_name'],
                    'role'     => $admin['role']
                ];

                set_flash('success', 'Selamat datang kembali, ' . $admin['full_name'] . '!');
                header('Location: index.php');
                exit;
            } else {
                $error = 'Username atau kata sandi tidak valid.';
            }
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $error = 'Terjadi kesalahan sistem saat mencoba masuk.';
        }
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin CMS | PT Aillena Marine Indonesia</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Admin Stylesheet -->
  <link rel="stylesheet" href="css/admin.css">
</head>
<body class="login-body">

  <div class="login-card">
    <div class="login-header">
      <div class="admin-logo-mark">
        <i class="fa-solid fa-anchor"></i>
      </div>
      <h2>Aillena Marine CMS</h2>
      <p>Masuk ke Panel Kelola & Backup Data Perusahaan</p>
    </div>

    <?php if ($flash): ?>
      <div class="admin-alert admin-alert-<?= $flash['type'] === 'danger' ? 'danger' : 'success' ?>">
        <i class="fa-solid <?= $flash['type'] === 'danger' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i>
        <span><?= htmlspecialchars($flash['message']) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="admin-alert admin-alert-danger">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div class="admin-form-group">
        <label for="username"><i class="fa-solid fa-user gold-text"></i> Username</label>
        <input type="text" id="username" name="username" class="admin-input" placeholder="Masukkan username admin" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>

      <div class="admin-form-group">
        <label for="password"><i class="fa-solid fa-lock gold-text"></i> Kata Sandi (Password)</label>
        <input type="password" id="password" name="password" class="admin-input" placeholder="••••••••" required>
      </div>

      <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-gold btn-block" style="width: 100%; padding: 0.85rem; font-size: 0.95rem;">
          <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Dashboard
        </button>
      </div>

      <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid var(--admin-navy-border); padding-top: 1rem;">
        <a href="../index.html" style="font-size: 0.82rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.4rem;">
          <i class="fa-solid fa-arrow-left"></i> Kembali ke Website Utama
        </a>
      </div>
    </form>
  </div>

</body>
</html>
