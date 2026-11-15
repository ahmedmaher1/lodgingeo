<?php
/**
 * Owner-only maintenance jobs, for erasure requests and post-deploy test cleanup.
 *
 * Upload a JSON job as public_html/.lodgingeo-maintenance.json (a dotfile, denied over HTTP;
 * only the hosting account can write it). The next request runs it once, deletes the job file
 * and writes the result to storage/maintenance-report.json (denied over HTTP).
 *
 *   {"lookup": ["a@example.com"], "erase": ["a@example.com"], "error_log_tail": 50, "clear_report": false}
 */
declare(strict_types=1);

function run_maintenance(): void
{
    $jobFile = (string) cfg('MAINTENANCE_JOB', ROOT . '/.lodgingeo-maintenance.json');
    $reportFile = (string) cfg('MAINTENANCE_REPORT', ROOT . '/storage/maintenance-report.json');
    if (!is_file($jobFile)) {
        return;
    }
    $job = json_decode((string) file_get_contents($jobFile), true);
    @unlink($jobFile);
    if (!is_array($job)) {
        return;
    }
    if (!empty($job['clear_report'])) {
        @unlink($reportFile);
        return;
    }
    $emails = fn(string $k) => array_values(array_filter(array_map(fn($e) => strtolower(trim((string) $e)), (array) ($job[$k] ?? []))));
    $pdo = db();
    $report = ['ran_at' => gmdate('c'), 'lookup' => [], 'erased' => []];

    foreach ($emails('lookup') as $email) {
        $l = $pdo->prepare('SELECT id, created_at, source_page, is_free_email, download_count, token_expires_at, SUBSTR(token, 1, 8) AS token_prefix FROM leads WHERE email = ?');
        $l->execute([$email]);
        $m = $pdo->prepare('SELECT id, created_at, form, data_needed FROM messages WHERE email = ?');
        $m->execute([$email]);
        $report['lookup'][$email] = ['leads' => $l->fetchAll(), 'messages' => $m->fetchAll()];
    }
    foreach ($emails('erase') as $email) {
        $a = $pdo->prepare('DELETE FROM leads WHERE email = ?');
        $a->execute([$email]);
        $b = $pdo->prepare('DELETE FROM messages WHERE email = ?');
        $b->execute([$email]);
        $c = $pdo->prepare('DELETE FROM rate_events WHERE kind = ? AND key_hash = ?');
        $c->execute(['email', email_key($email)]);
        $report['erased'][$email] = ['leads' => $a->rowCount(), 'messages' => $b->rowCount(), 'rate_events' => $c->rowCount()];
    }
    $n = (int) ($job['error_log_tail'] ?? 0);
    if ($n > 0) {
        $log = (string) cfg('ERROR_LOG', '');
        $lines = is_file($log) ? (file($log, FILE_IGNORE_NEW_LINES) ?: []) : [];
        $report['error_log_tail'] = array_slice($lines, -min($n, 200));
    }
    @file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
}
