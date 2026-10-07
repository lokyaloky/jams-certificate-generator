<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config.php';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf'];
$today = date('Y-m-d');
$ratings = ['Outstanding','Excellent','Very Good','Good','Fair','Poor'];
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e(APP_NAME) ?></title>
<style>
:root{--blue:#4b82c3;--navy:#1f3552;--border:#cbd3dc;--bg:#f4f7fa}*{box-sizing:border-box}body{margin:0;background:var(--bg);font-family:Arial,Helvetica,sans-serif;color:#1d2a38}.wrap{max-width:1180px;margin:28px auto;padding:0 18px}.card{background:#fff;border-radius:10px;box-shadow:0 8px 30px rgba(31,53,82,.12);padding:28px}.brand{text-align:center;margin-bottom:24px}.brand h1{margin:0;color:#1f3552;font-size:25px}.brand p{margin:7px 0;color:#6b7785}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.full{grid-column:1/-1}.field{display:flex;flex-direction:column;gap:6px}label{font-size:13px;font-weight:700;color:#2d4664}input,select,textarea{font:14px Arial,sans-serif;border:1px solid var(--border);border-radius:5px;padding:10px 11px;width:100%;background:#fff}textarea{min-height:110px;resize:vertical}input:focus,select:focus,textarea:focus{outline:2px solid rgba(75,130,195,.18);border-color:var(--blue)}.section{grid-column:1/-1;color:var(--blue);font-size:18px;margin-top:6px;padding-bottom:5px;border-bottom:1px solid #dfe6ed}.rating-grid{display:grid;grid-template-columns:1.6fr repeat(6,1fr);border-top:1px solid #222;border-left:1px solid #222}.rating-grid>div{border-right:1px solid #222;border-bottom:1px solid #222;padding:9px 6px;font-size:12px}.rating-grid .head{text-align:center;font-weight:700}.rating-grid .crit{font-weight:600}.rating-grid label{text-align:center;cursor:pointer}.actions{display:flex;gap:10px;margin-top:22px}.btn{border:0;border-radius:5px;padding:12px 18px;font-weight:700;cursor:pointer}.primary{background:#173d70;color:#fff}.secondary{background:#fff;color:#173d70;border:1px solid #173d70}.status{display:none;margin-top:18px;padding:13px;border-radius:6px}.success{background:#edf8f0;color:#216b38}.error{background:#fff0ee;color:#8a2c23}.spinner{display:none}.download{display:inline-block;margin-top:10px;color:#fff;background:#2e7d46;padding:11px 16px;border-radius:5px;text-decoration:none;font-weight:700}.small{font-size:12px;color:#6c7783}.recommendation{display:grid;grid-template-columns:1fr 1fr;gap:10px}.recommendation label{border:1px solid var(--border);padding:10px;border-radius:5px;font-weight:600}.recommendation input{width:auto;margin-right:7px}@media(max-width:800px){.grid{grid-template-columns:1fr}.full{grid-column:auto}.rating-grid{grid-template-columns:1.5fr repeat(6,70px);overflow-x:auto}.recommendation{grid-template-columns:1fr}.card{padding:20px}.wrap{padding:0 10px}}
</style></head><body><div class="wrap"><div class="card">
<div class="brand"><h1>JOURNAL OF ADVANCED MULTIDISCIPLINARY STUDIES (JAMS)</h1><p>Peer Review Evaluation Form Generator</p><div class="small">Creates a server-generated, selectable-text PDF based on the uploaded JAMS reference document.</div></div>
<form id="reviewForm">
<input type="hidden" name="csrf" value="<?= e($csrf) ?>">
<div class="grid">
<div class="section">Manuscript Information</div>
<div class="field"><label>Article ID *</label><input name="article_id" required placeholder="JAMS-2026-29"></div>
<div class="field"><label>Paper Title *</label><input name="paper_title" required></div>
<div class="field"><label>Authors Name *</label><input name="authors" required></div>
<div class="field"><label>Corresponding Author *</label><input name="corresponding_author" required></div>
<div class="field"><label>Submission Date *</label><input type="date" name="submission_date" required></div>
<div class="field"><label>Review Date *</label><input type="date" name="review_date" value="<?= e($today) ?>" required></div>

<div class="section">Evaluation Criteria</div>
<div class="full"><div class="rating-grid">
<div class="head">Criteria</div><?php foreach($ratings as $r): ?><div class="head"><?= e($r) ?></div><?php endforeach; ?>
<?php $criteria=[['originality','Originality'],['technical_quality','Technical Quality'],['presentation','Presentation & Organization'],['language_quality','Language Quality'],['references','References'],['practical_applicability','Practical Applicability'],['overall_quality','Overall Quality']]; foreach($criteria as [$name,$label]): ?><div class="crit"><?= e($label) ?></div><?php foreach($ratings as $r): ?><div><label><input type="radio" name="<?= e($name) ?>" value="<?= e($r) ?>" <?= $r==='Good'?'checked':'' ?> required></label></div><?php endforeach; ?><?php endforeach; ?>
</div></div>

<div class="section">Reviewer Comments</div>
<div class="field full"><label>Strengths *</label><textarea name="strengths" required placeholder="Enter the strengths exactly as they should appear in the PDF."></textarea></div>
<div class="field full"><label>Weaknesses *</label><textarea name="weaknesses" required></textarea></div>
<div class="field full"><label>Suggestions for Improvement *</label><textarea name="suggestions" required></textarea></div>

<div class="section">Final Recommendation</div>
<div class="field full"><div class="recommendation"><?php foreach(['Accept','Accept with Minor Revisions','Major Revisions Required','Resubmit after Revision','Reject'] as $i=>$r): ?><label><input type="radio" name="recommendation" value="<?= e($r) ?>" <?= $i===1?'checked':'' ?> required><?= e($r) ?></label><?php endforeach; ?></div></div>
</div>
<div class="actions"><button class="btn primary" id="generate" type="submit">Generate PDF</button><button class="btn secondary" type="reset">Clear Form</button></div>
</form>
<div id="status" class="status"></div>
</div></div>
<script>
const form=document.getElementById('reviewForm'),btn=document.getElementById('generate'),statusBox=document.getElementById('status');
form.addEventListener('submit',async e=>{e.preventDefault();statusBox.style.display='none';btn.disabled=true;btn.textContent='Generating PDF...';try{const r=await fetch('generate.php',{method:'POST',body:new FormData(form)});const data=await r.json();if(!r.ok||!data.ok)throw new Error(data.error||'PDF generation failed.');statusBox.className='status success';statusBox.style.display='block';statusBox.innerHTML='<strong>PDF Generated Successfully</strong><br><span>'+data.filename+'</span><br><a class="download" href="'+data.download_url+'">Download PDF</a> <button class="btn secondary" type="button" onclick="location.reload()">Generate Another PDF</button>';}catch(err){statusBox.className='status error';statusBox.style.display='block';statusBox.textContent=err.message;}finally{btn.disabled=false;btn.textContent='Generate PDF';}});
</script></body></html>
