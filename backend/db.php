<?php

define('DB_PATH', __DIR__ . '/database.sqlite');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        initDB($pdo);
    }
    return $pdo;
}

function initDB(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id        INTEGER PRIMARY KEY AUTOINCREMENT,
            email     TEXT    NOT NULL UNIQUE,
            password  TEXT    NOT NULL,
            totp_secret TEXT  DEFAULT NULL,
            totp_enabled INTEGER NOT NULL DEFAULT 0,
            created_at TEXT   NOT NULL DEFAULT (datetime('now'))
        )
    ");
}
