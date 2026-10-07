<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

ensureStorage();
$id = (string)($_GET['id'] ?? '');
if (!preg_match('/^[a-f0-9]{48}$/', $id)) {
    http_response_code(404); exit('File not found.');
}

$metaPath = STORAGE_META_DIR . '/' . $id . '.json';
$pdfPath = STORAGE_DIR . '/' . $id . '.pdf';
if (!is_file($metaPath) || !is_file($pdfPath)) {
    http_response_code(404); exit('File not found or expired.');
}

$meta = json_decode((string)file_get_contents($metaPath), true);
if (!is_array($meta) || empty($meta['expires_at']) || time() > (int)$meta['expires_at']) {
    @unlink($metaPath); @unlink($pdfPath);
    http_response_code(410); exit('This PDF link has expired.');
}

$filename = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)($meta['filename'] ?? 'JAMS_Peer_Review.pdf')) ?: 'JAMS_Peer_Review.pdf';
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($pdfPath));
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('X-Content-Type-Options: nosniff');
readfile($pdfPath);
