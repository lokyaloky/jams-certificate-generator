<?php
declare(strict_types=1);

const APP_NAME = 'JAMS Peer Review Evaluation Generator';
const STORAGE_DIR = __DIR__ . '/storage/generated';
const STORAGE_META_DIR = __DIR__ . '/storage/meta';
const STORAGE_TTL = 86400; // 24 hours
const MAX_TEXT_LENGTH = 10000;

function ensureStorage(): void {
    foreach ([STORAGE_DIR, STORAGE_META_DIR] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
    }
}

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function cleanText(string $value, int $max = MAX_TEXT_LENGTH): string {
    $value = trim($value);
    if (mb_strlen($value) > $max) {
        $value = mb_substr($value, 0, $max);
    }
    return $value;
}

function postString(string $key, bool $required = false, int $max = MAX_TEXT_LENGTH): string {
    $value = cleanText((string)($_POST[$key] ?? ''), $max);
    if ($required && $value === '') {
        throw new InvalidArgumentException("Missing required field: {$key}");
    }
    return $value;
}

function validDate(string $value, bool $required = false): string {
    if ($value === '') {
        if ($required) throw new InvalidArgumentException('Invalid or missing date.');
        return '';
    }
    $d = DateTime::createFromFormat('Y-m-d', $value);
    if (!$d || $d->format('Y-m-d') !== $value) {
        throw new InvalidArgumentException('Invalid date format.');
    }
    return $value;
}

function formatDate(string $value): string {
    if ($value === '') return '-';
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d ? $d->format('d-m-Y') : '-';
}

function allowedRating(string $value): string {
    $allowed = ['Outstanding', 'Excellent', 'Very Good', 'Good', 'Fair', 'Poor'];
    if (!in_array($value, $allowed, true)) {
        throw new InvalidArgumentException('Invalid evaluation rating.');
    }
    return $value;
}

function allowedRecommendation(string $value): string {
    $allowed = ['Accept', 'Accept with Minor Revisions', 'Major Revisions Required', 'Resubmit after Revision', 'Reject'];
    if (!in_array($value, $allowed, true)) {
        throw new InvalidArgumentException('Invalid recommendation.');
    }
    return $value;
}

function jsonResponse(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
