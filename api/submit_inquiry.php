<?php
/**
 * API: Submit Client Consultation / Inquiry
 * Handles incoming consultation forms, saves to database, and responds with WhatsApp link.
 */

header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config/database.php';

// Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Metode request tidak didukung.'], 405);
}

// Get input data (support both standard POST form-data and JSON payload)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?? [];
} else {
    $data = $_POST;
}

$name    = sanitize($data['clientName'] ?? '');
$org     = sanitize($data['clientOrg'] ?? '-');
$phone   = sanitize($data['clientPhone'] ?? '');
$service = sanitize($data['serviceType'] ?? '');
$boat    = sanitize($data['boatDetails'] ?? '-');
$message = sanitize($data['message'] ?? '');

// Validation
if (empty($name) || empty($phone) || empty($service) || empty($message)) {
    json_response([
        'success' => false,
        'message' => 'Mohon lengkapi semua kolom wajib (Nama, No. Telepon, Layanan, dan Rincian Kebutuhan).'
    ], 422);
}

try {
    $pdo = get_db_connection();

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO inquiries (client_name, client_org, client_phone, service_type, boat_details, message, status)
        VALUES (:name, :org, :phone, :service, :boat, :message, 'Baru')
    ");

    $stmt->execute([
        ':name'    => $name,
        ':org'     => $org,
        ':phone'   => $phone,
        ':service' => $service,
        ':boat'    => $boat,
        ':message' => $message
    ]);

    $inquiryId = $pdo->lastInsertId();

    // Retrieve Admin WhatsApp number from settings
    $settingStmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'company_whatsapp'");
    $settingStmt->execute();
    $adminWa = $settingStmt->fetchColumn() ?: '6282323212323';

    // Format WhatsApp Message
    $waText = 
"*PERMINTAAN KONSULTASI / LAYANAN*
*PT AILLENA MARINE INDONESIA*
-----------------------------------
*No. Tiket Inquiry:* #AMI-{$inquiryId}
*Nama:* {$name}
*Instansi/Perusahaan:* {$org}
*No. Telepon/WA:* {$phone}
*Layanan yang Dibutuhkan:* {$service}
*Info Kapal/Mesin:* {$boat}
*Rincian Kebutuhan:*
{$message}
-----------------------------------
_Dikirim via Website Resmi Aillena Marine_";

    $waUrl = "https://wa.me/{$adminWa}?text=" . urlencode($waText);

    json_response([
        'success'    => true,
        'message'    => 'Permintaan konsultasi Anda berhasil dikirim dan tersimpan di database.',
        'inquiry_id' => $inquiryId,
        'wa_url'     => $waUrl
    ], 201);

} catch (Exception $e) {
    error_log('Error saving inquiry: ' . $e->getMessage());
    json_response([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem saat menyimpan permintaan. Silakan hubungi kami langsung via WhatsApp.'
    ], 500);
}
