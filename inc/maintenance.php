<?php
/**
 * Owner-only maintenance jobs, for erasure requests and post-deploy test cleanup.
 *
 * Upload a JSON job as public_html/.lodgingeo-maintenance.json (a dotfile, denied over HTTP;
 * only the hosting account can write it). The next request runs it once, deletes the job file
 * and writes the result to storage/maintenance-report.json (denied over HTTP).
 *
 *   {"lookup": ["a@example.com"], "erase": ["a@example.com"], "error_log_tail": 50, "clear_report": false}
 *   {"export": true}                         full backup of leads and messages into the report
 *   {"import": {"leads": [...], "messages": [...]}}   restore rows from an export (ids are reassigned)
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
    if (!empty($job['export'])) {
        $report['export'] = [
            'leads' => $pdo->query('SELECT * FROM leads ORDER BY id')->fetchAll(),
            'messages' => $pdo->query('SELECT * FROM messages ORDER BY id')->fetchAll(),
        ];
    }
    if (isset($job['import']) && is_array($job['import'])) {
        $report['imported'] = [];
        foreach (['leads', 'messages'] as $table) {
            $cols = maintenance_columns($table);
            $count = 0;
            foreach ((array) ($job['import'][$table] ?? []) as $row) {
                $row = array_intersect_key((array) $row, array_flip($cols));
                if (!$row) {
                    continue;
                }
                $names = array_keys($row);
                $sql = 'INSERT INTO ' . $table . ' (' . implode(',', $names) . ') VALUES (' . implode(',', array_fill(0, count($names), '?')) . ')';
                $pdo->prepare($sql)->execute(array_values($row));
                $count++;
            }
            $report['imported'][$table] = $count;
        }
    }
    $n = (int) ($job['error_log_tail'] ?? 0);
    if ($n > 0) {
        $log = (string) cfg('ERROR_LOG', '');
        $lines = is_file($log) ? (file($log, FILE_IGNORE_NEW_LINES) ?: []) : [];
        $report['error_log_tail'] = array_slice($lines, -min($n, 200));
    }
    @file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
}

/** Columns that may be restored (never the id). */
function maintenance_columns(string $table): array
{
    return $table === 'leads'
        ? ['created_at', 'name', 'email', 'email_domain', 'is_free_email', 'company', 'countries', 'marketing_opt_in', 'source_page',
           'landing_page', 'referrer', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'ip_hash', 'user_agent', 'token',
           'token_expires_at', 'download_count', 'first_downloaded_at', 'last_downloaded_at']
        : ['created_at', 'form', 'name', 'email', 'company', 'countries', 'data_needed', 'format', 'frequency', 'message', 'source_page',
           'landing_page', 'referrer', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'ip_hash', 'user_agent'];
}
