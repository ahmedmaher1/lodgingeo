<?php
/**
 * PDO connection plus self-migration. The DSN comes from the server config,
 * so production uses MySQL and tests use SQLite.
 */
declare(strict_types=1);

const SCHEMA_VERSION = 1;

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $pdo = new PDO((string) cfg('DB_DSN'), (string) cfg('DB_USER', ''), (string) cfg('DB_PASS', ''), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 3000');
    }
    db_migrate($pdo);
    return $pdo;
}

function db_migrate(PDO $pdo): void
{
    $mysql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
    $id    = $mysql ? 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $tail  = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';

    $pdo->exec("CREATE TABLE IF NOT EXISTS schema_version (version INT NOT NULL, applied_at VARCHAR(19) NOT NULL)$tail");
    $current = (int) $pdo->query('SELECT COALESCE(MAX(version), 0) FROM schema_version')->fetchColumn();
    if ($current >= SCHEMA_VERSION) {
        return;
    }

    if ($current < 1) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS leads (
            id $id,
            created_at VARCHAR(19) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(254) NOT NULL,
            email_domain VARCHAR(253) NOT NULL,
            is_free_email TINYINT NOT NULL DEFAULT 0,
            company VARCHAR(200) NULL,
            countries VARCHAR(500) NULL,
            marketing_opt_in TINYINT NOT NULL DEFAULT 0,
            source_page VARCHAR(300) NULL,
            landing_page VARCHAR(500) NULL,
            referrer VARCHAR(500) NULL,
            utm_source VARCHAR(200) NULL,
            utm_medium VARCHAR(200) NULL,
            utm_campaign VARCHAR(200) NULL,
            utm_term VARCHAR(200) NULL,
            ip_hash CHAR(64) NOT NULL,
            user_agent VARCHAR(500) NULL,
            token CHAR(64) NOT NULL,
            token_expires_at VARCHAR(19) NOT NULL,
            download_count INT NOT NULL DEFAULT 0,
            first_downloaded_at VARCHAR(19) NULL,
            last_downloaded_at VARCHAR(19) NULL
        )$tail");
        $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
            id $id,
            created_at VARCHAR(19) NOT NULL,
            form VARCHAR(20) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(254) NOT NULL,
            company VARCHAR(200) NULL,
            countries VARCHAR(500) NULL,
            data_needed VARCHAR(500) NULL,
            format VARCHAR(40) NULL,
            frequency VARCHAR(40) NULL,
            message TEXT NOT NULL,
            source_page VARCHAR(300) NULL,
            landing_page VARCHAR(500) NULL,
            referrer VARCHAR(500) NULL,
            utm_source VARCHAR(200) NULL,
            utm_medium VARCHAR(200) NULL,
            utm_campaign VARCHAR(200) NULL,
            utm_term VARCHAR(200) NULL,
            ip_hash CHAR(64) NOT NULL,
            user_agent VARCHAR(500) NULL
        )$tail");
        $pdo->exec("CREATE TABLE IF NOT EXISTS rate_events (
            id $id,
            kind VARCHAR(20) NOT NULL,
            key_hash CHAR(64) NOT NULL,
            created_at VARCHAR(19) NOT NULL
        )$tail");
        foreach ([
            'CREATE INDEX idx_leads_token ON leads (token)',
            'CREATE INDEX idx_leads_email ON leads (email, created_at)',
            'CREATE INDEX idx_rate ON rate_events (kind, key_hash, created_at)',
        ] as $sql) {
            try {
                $pdo->exec($sql);
            } catch (PDOException) {
                // Index already exists.
            }
        }
        $stmt = $pdo->prepare('INSERT INTO schema_version (version, applied_at) VALUES (?, ?)');
        $stmt->execute([1, gmdate('Y-m-d H:i:s')]);
    }
}
