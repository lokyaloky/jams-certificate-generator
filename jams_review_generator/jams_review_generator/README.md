# JAMS Peer Review Evaluation Form – Form-to-PDF Generator

This implementation reproduces the uploaded JAMS Peer Review Evaluation Form as a server-generated A4 PDF. The PDF is generated with selectable text using mPDF rather than converting a browser screenshot to an image.

## Source design reproduced
- A4 portrait page size.
- JAMS centered title block and underlined hierarchy.
- JAMS logo from the supplied DOCX.
- Blue italic website/email lines.
- Blue section headings.
- Manuscript information table.
- Seven-row evaluation matrix with six rating columns.
- Strengths, Weaknesses and Suggestions for Improvement sections.
- Final Recommendation table with checkbox-style selection.
- Two-page structure matching the supplied reference: the second page begins with Weaknesses.
- Footer text on both pages: JOURNAL OF ADVANCED MULTIDISCIPLINARY STUDIES / (JAMS).

The reference itself contains no visible page numbers, so none were added.

## Requirements
- PHP 8.x recommended.
- Composer.
- PHP extensions normally required by mPDF, including mbstring and GD where applicable.
- Writable `storage/generated` and `storage/meta` directories.

## Install
1. Upload the complete project to your PHP hosting account.
2. From the project root run:
   `composer install --no-dev --optimize-autoloader`
3. Ensure the web server can write to `storage/generated` and `storage/meta`.
4. Open `index.php` in the browser.

## Workflow
Fill Form → Generate PDF → server validates data → mPDF creates selectable-text PDF → a random 48-hex token is generated → the PDF is stored outside direct download access → `download.php` streams it as an attachment.

Generated links expire after 24 hours by default. Change `STORAGE_TTL` in `config.php` if needed.

## Security
- CSRF token validation.
- Server-side allow-list validation for ratings and recommendations.
- Server-side date validation.
- Length limits for text inputs.
- HTML escaping before PDF rendering.
- Random, unguessable download tokens.
- Generated files are blocked from direct Apache access by `.htaccess`.
- Filenames are sanitized.
- User input is never treated as raw HTML.

## Important
Do not allow arbitrary user-supplied HTML/CSS to be passed to mPDF. This implementation escapes all form text before inserting it into the PDF template.

## Customization
Edit `template.php` for PDF appearance and `index.php` for the form UI. The reference logo is in `assets/jams-logo.png`.
