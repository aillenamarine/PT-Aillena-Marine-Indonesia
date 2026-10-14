<?php
/**
 * PT Aillena Marine Indonesia - Database Connection & Auto-Migrator
 * Uses PDO SQLite for zero-config portability and easy one-click backups.
 */

require_once __DIR__ . '/config.php';

function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbFile = DATA_PATH . '/aillena_marine.db';
    $isNewDb = !file_exists($dbFile);

    try {
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Auto-initialize schema if new or missing tables
        init_db_schema($pdo, $isNewDb);

        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        die('Koneksi database gagal. Mohon pastikan direktori data dapat ditulis (writable).');
    }
}

/**
 * Initialize Tables & Default Seed Data
 */
function init_db_schema($pdo, $isNewDb) {
    // 1. Admins Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            full_name TEXT NOT NULL,
            role TEXT DEFAULT 'admin',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // 2. Gallery Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gallery (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            category TEXT NOT NULL,
            image_url TEXT NOT NULL,
            location TEXT DEFAULT 'Workshop Memey Marine',
            tags TEXT,
            display_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // 3. Inquiries Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS inquiries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            client_name TEXT NOT NULL,
            client_org TEXT,
            client_phone TEXT NOT NULL,
            service_type TEXT NOT NULL,
            boat_details TEXT,
            message TEXT NOT NULL,
            status TEXT DEFAULT 'Baru', -- 'Baru', 'Dihubungi', 'Sedang Diproses', 'Selesai', 'Dibatalkan'
            admin_notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // 4. Site Settings Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS site_settings (
            setting_key TEXT PRIMARY KEY,
            setting_value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Seed default admin if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins");
    $adminCount = $stmt->fetch()['count'];
    if ($adminCount == 0) {
        $defaultPassword = password_hash('AdminMarine2026!', PASSWORD_BCRYPT);
        $insertAdmin = $pdo->prepare("
            INSERT INTO admins (username, password_hash, full_name, role) 
            VALUES (:username, :password, :name, 'superadmin')
        ");
        $insertAdmin->execute([
            ':username' => 'admin',
            ':password' => $defaultPassword,
            ':name'     => 'Administrator Aillena'
        ]);
    }

    // Seed default site settings if empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM site_settings");
    $settingsCount = $stmt->fetch()['count'];
    if ($settingsCount == 0) {
        $defaultSettings = [
            'company_name'       => 'PT Aillena Marine Indonesia',
            'company_tagline'    => 'Solusi Perawatan Armada Laut & Mesin Kapal',
            'company_phone'      => '082323212323',
            'company_whatsapp'   => '6282323212323',
            'company_email'      => 'ptaillenamarineindonesia@gmail.com',
            'company_address'    => 'Jl. Sendowo No. 88, Purwodinatan, Semarang Tengah, Kota Semarang 50137',
            'operating_hours'    => 'Senin – Sabtu: 08.00 – 17.00 WIB',
            'emergency_notice'   => 'Layanan Darurat Siaga 24 Jam (On-Call Dermaga/Pelabuhan)',
            'inaproc_lkpp_url'   => 'https://katalog.inaproc.id/aillena-marine-indonesia?page=2',
            'maps_embed_url'     => 'https://maps.google.com/?q=Jl.+Sendowo+No.88+Purwodinatan+Semarang'
        ];

        $insertSetting = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value) 
            VALUES (:key, :val)
        ");
        foreach ($defaultSettings as $k => $v) {
            $insertSetting->execute([':key' => $k, ':val' => $v]);
        }
    }

    // Seed initial gallery if empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery");
    $galleryCount = $stmt->fetch()['count'];
    if ($galleryCount == 0) {
        $defaultGallery = [
            [
                'title'       => 'Pengecatan Presisi & Rekondisi Transom',
                'description' => 'Teknisi berseragam resmi dengan helm dan respirator mengaplikasikan cat maritim berkualitas tinggi pada bodi speedboat bermesin ganda.',
                'category'    => 'Pengecatan Bodi',
                'image_url'   => 'assets/images/documentation/dokumentasi-pengecatan-bodi-kapal.jpg',
                'location'    => 'Workshop Memey Marine',
                'tags'        => 'Fiber, Cat, K3',
                'order'       => 1
            ],
            [
                'title'       => 'Pelapisan Antifouling Lambung di Jalur Rel',
                'description' => 'Pengecatan protektif anti-teritip laut pada lambung bawah kapal di atas bantalan rel kereta luncur muara untuk menjaga ketahanan laju kapal.',
                'category'    => 'Docking Rel',
                'image_url'   => 'assets/images/documentation/dokumentasi-antifouling-docking-rel.jpg',
                'location'    => 'Jalur Rel Slipway Muara',
                'tags'        => 'Antifouling, Docking, Slipway',
                'order'       => 2
            ],
            [
                'title'       => 'Servis & Kalibrasi Lower Unit Yamaha',
                'description' => 'Mekanik bersertifikat membongkar dan merakit lower unit gearcase serta propeller stainless steel pada mesin tempel Yamaha dengan standar pabrikan.',
                'category'    => 'Overhaul Mesin',
                'image_url'   => 'assets/images/documentation/dokumentasi-overhaul-lower-unit-yamaha.jpg',
                'location'    => 'Bengkel Mesin Memey Marine',
                'tags'        => 'Yamaha, OBM, Lower Unit',
                'order'       => 3
            ],
            [
                'title'       => 'Penyetelan Mesin Ganda Yamaha Four Stroke 100 HP',
                'description' => 'Sinkronisasi karburator/injeksi dan diagnosa RPM pada instalasi twin-outboard engine untuk armada patroli berkecepatan tinggi.',
                'category'    => 'Overhaul Mesin',
                'image_url'   => 'assets/images/documentation/dokumentasi-twin-engine-100hp-yamaha.jpg',
                'location'    => 'Workshop Memey Marine',
                'tags'        => 'Twin Engine, 100HP, 4-Tak',
                'order'       => 4
            ],
            [
                'title'       => 'Inspeksi Mesin Tempel Sebelum Uji Coba Laut',
                'description' => 'Pemeriksaan kompresi silinder, sirkulasi pendingin, dan kelistrikan starter sebelum kapal diturunkan ke perairan uji coba.',
                'category'    => 'Sea Trial',
                'image_url'   => 'assets/images/documentation/dokumentasi-inspeksi-mesin-pra-seatrial.jpg',
                'location'    => 'Dermaga Uji Muara',
                'tags'        => 'Sea Trial, QC, Inspeksi',
                'order'       => 5
            ]
        ];

        $insertGallery = $pdo->prepare("
            INSERT INTO gallery (title, description, category, image_url, location, tags, display_order)
            VALUES (:title, :description, :category, :image_url, :location, :tags, :order)
        ");
        foreach ($defaultGallery as $g) {
            $insertGallery->execute([
                ':title'       => $g['title'],
                ':description' => $g['description'],
                ':category'    => $g['category'],
                ':image_url'   => $g['image_url'],
                ':location'    => $g['location'],
                ':tags'        => $g['tags'],
                ':order'       => $g['order']
            ]);
        }
    }
}
