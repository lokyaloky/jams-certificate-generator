<?php
declare(strict_types=1);

function reviewPdfHtml(array $d): string {
    $criteria = [
        'Originality' => $d['originality'],
        'Technical Quality' => $d['technical_quality'],
        'Presentation & Organization' => $d['presentation'],
        'Language Quality' => $d['language_quality'],
        'References' => $d['references'],
        'Practical Applicability' => $d['practical_applicability'],
        'Overall Quality' => $d['overall_quality'],
    ];
    $ratings = ['Outstanding', 'Excellent', 'Very Good', 'Good', 'Fair', 'Poor'];
    $logo = __DIR__ . '/assets/jams-logo.png';
    $logoSrc = is_file($logo) ? 'data:image/png;base64,' . base64_encode((string)file_get_contents($logo)) : '';
    $recommendations = ['Accept', 'Accept with Minor Revisions', 'Major Revisions Required', 'Resubmit after Revision', 'Reject'];

    $ratingRows = '';
    foreach ($criteria as $label => $selected) {
        $ratingRows .= '<tr><td>' . e($label) . '</td>';
        foreach ($ratings as $rating) {
            $ratingRows .= '<td class="rating">' . ($selected === $rating ? '<span class="dot">●</span>' : '') . '</td>';
        }
        $ratingRows .= '</tr>';
    }

    $recommendationRows = '';
    foreach ($recommendations as $rec) {
        $recommendationRows .= '<tr><td>' . e($rec) . '</td><td class="select-box">' . ($d['recommendation'] === $rec ? '☒' : '☐') . '</td></tr>';
    }

    return '<!doctype html><html><head><meta charset="utf-8"><style>
    @page { size: A4 portrait; margin: 12.5mm 31.5mm 12.5mm 31.5mm; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color:#111; margin:0; }
    .header { text-align:center; }
    .journal { font-size:16.5pt; font-weight:700; text-decoration:underline; line-height:1.15; }
    .jams { font-size:16.5pt; font-weight:700; text-decoration:underline; margin-top:2px; }
    .form-title { font-size:15.5pt; font-weight:700; text-decoration:underline; margin-top:2px; }
    .logo { width:30mm; height:auto; margin-top:3mm; }
    .contact { color:#4a83c5; font-style:italic; font-size:10.5pt; line-height:1.45; margin-top:2mm; }
    .contact u { text-decoration:underline; }
    h2 { color:#4b82c3; font-size:13.5pt; margin:5mm 0 2mm; font-weight:700; }
    table { width:100%; border-collapse:collapse; }
    .info td { border:0.25mm solid #222; padding:1.3mm 1.6mm; font-size:9.4pt; vertical-align:top; }
    .info td:first-child { width:31%; }
    .criteria th,.criteria td { border:0.25mm solid #222; padding:1.5mm 1mm; font-size:8.8pt; }
    .criteria th { font-weight:400; text-align:center; }
    .criteria th:first-child,.criteria td:first-child { text-align:left; width:31%; }
    .criteria .rating { text-align:center; width:11.5%; height:6.2mm; }
    .dot { font-size:10pt; }
    .section-text { font-size:10pt; line-height:1.28; text-align:justify; margin:0; }
    .recommendation { margin-top:2mm; }
    .recommendation td { padding:2.2mm 0; font-size:10pt; }
    .recommendation tr:first-child td { font-weight:700; padding-bottom:3mm; }
    .recommendation td:first-child { width:78%; }
    .select-box { text-align:left; font-size:12pt; }
    .footer { font-family:Georgia,serif; font-size:9pt; color:#111; }
    .footer-left { text-align:left; }
    .footer-right { text-align:right; }
    .pagebreak { page-break-before:always; }
    </style></head><body>
    <div class="header">
      <div class="journal">JOURNAL OF ADVANCED MULTIDISCIPLINARY STUDIES</div>
      <div class="jams">(JAMS)</div>
      <div class="form-title">Peer Review Evaluation Form</div>
      ' . ($logoSrc ? '<img class="logo" src="' . $logoSrc . '" alt="JAMS logo">' : '') . '
      <div class="contact"><u>Website: https://jamsjournal.org</u><br><u>Email: info@jamsjournal.org</u></div>
    </div>

    <h2>Manuscript Information</h2>
    <table class="info">
      <tr><td>Article ID</td><td>' . e($d['article_id']) . '</td></tr>
      <tr><td>Paper Title</td><td>' . e($d['paper_title']) . '</td></tr>
      <tr><td>Authors Name</td><td>' . e($d['authors']) . '</td></tr>
      <tr><td>Corresponding Author</td><td>' . e($d['corresponding_author']) . '</td></tr>
      <tr><td>Submission Date</td><td>' . e(formatDate($d['submission_date'])) . '</td></tr>
      <tr><td>Review Date</td><td>' . e(formatDate($d['review_date'])) . '</td></tr>
    </table>

    <h2>Evaluation Criteria</h2>
    <table class="criteria"><thead><tr><th>Criteria</th><th>Outstanding</th><th>Excellent</th><th>Very Good</th><th>Good</th><th>Fair</th><th>Poor</th></tr></thead><tbody>' . $ratingRows . '</tbody></table>

    <h2>Strengths</h2><p class="section-text">' . nl2br(e($d['strengths'])) . '</p>

    <div class="pagebreak"></div>
    <h2>Weaknesses</h2><p class="section-text">' . nl2br(e($d['weaknesses'])) . '</p>

    <h2>Suggestions for Improvement</h2><p class="section-text">' . nl2br(e($d['suggestions'])) . '</p>

    <h2>Final Recommendation</h2>
    <table class="recommendation"><tr><td>Recommendation</td><td>Select</td></tr>' . $recommendationRows . '</table>
    </body></html>';
}
