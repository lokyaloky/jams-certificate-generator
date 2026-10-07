<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok'=>false,'error'=>'Invalid request method.'],405);
}

try {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        throw new RuntimeException('Security validation failed. Please refresh the page and try again.');
    }

    $d = [
        'article_id' => postString('article_id', true, 100),
        'paper_title' => postString('paper_title', true, 1000),
        'authors' => postString('authors', true, 500),
        'corresponding_author' => postString('corresponding_author', true, 500),
        'submission_date' => validDate(postString('submission_date', true), true),
        'review_date' => validDate(postString('review_date', true), true),
        'originality' => allowedRating(postString('originality', true, 30)),
        'technical_quality' => allowedRating(postString('technical_quality', true, 30)),
        'presentation' => allowedRating(postString('presentation', true, 30)),
        'language_quality' => allowedRating(postString('language_quality', true, 30)),
        'references' => allowedRating(postString('references', true, 30)),
        'practical_applicability' => allowedRating(postString('practical_applicability', true, 30)),
        'overall_quality' => allowedRating(postString('overall_quality', true, 30)),
        'strengths' => postString('strengths', true),
        'weaknesses' => postString('weaknesses', true),
        'suggestions' => postString('suggestions', true),
        'recommendation' => allowedRecommendation(postString('recommendation', true, 50)),
    ];

    if (!class_exists('\Mpdf\Mpdf')) {
        throw new RuntimeException('mPDF is not installed. Run composer install on the server.');
    }

    ensureStorage();

    $token = bin2hex(random_bytes(24));
    $safeId = preg_replace('/[^A-Za-z0-9_-]+/', '_', $d['article_id']) ?: 'Article';
    $datePart = date('Y-m-d');
    $filename = 'JAMS_Peer_Review_' . $safeId . '_' . $datePart . '.pdf';
    $pdfPath = STORAGE_DIR . '/' . $token . '.pdf';
    $metaPath = STORAGE_META_DIR . '/' . $token . '.json';

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'margin_top' => 12.5,
        'margin_bottom' => 12.5,
        'margin_left' => 31.5,
        'margin_right' => 31.5,
        'tempDir' => STORAGE_DIR . '/tmp'
    ]);
    $mpdf->SetTitle('JAMS Peer Review Evaluation Form - ' . $d['article_id']);
    $mpdf->SetAuthor('Journal of Advanced Multidisciplinary Studies (JAMS)');
    $mpdf->SetHTMLFooter('<table width="100%" style="font-family:Georgia,serif;font-size:9pt;color:#111;"><tr><td width="50%">JOURNAL OF ADVANCED MULTIDISCIPLINARY STUDIES</td><td width="50%" align="right">(JAMS)</td></tr></table>');
    $mpdf->WriteHTML(reviewPdfHtml($d));
    $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);

    file_put_contents($metaPath, json_encode([
        'filename' => $filename,
        'created_at' => time(),
        'expires_at' => time() + STORAGE_TTL,
        'size' => filesize($pdfPath),
        'article_id' => $d['article_id']
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    $downloadUrl = 'download.php?id=' . rawurlencode($token);
    jsonResponse(['ok'=>true,'filename'=>$filename,'download_url'=>$downloadUrl]);
} catch (Throwable $e) {
    error_log('[JAMS PDF] ' . $e->getMessage());
    jsonResponse(['ok'=>false,'error'=>$e->getMessage()],400);
}
