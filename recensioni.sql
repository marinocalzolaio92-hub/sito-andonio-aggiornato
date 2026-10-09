-- =============================================
-- Antonio Farella Hair Designer Studio
-- Schema SQL per Database Recensioni
-- =============================================

CREATE DATABASE IF NOT EXISTS antonio_farella
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE antonio_farella;

-- =============================================
-- TABELLA RECENSIONI
-- =============================================
CREATE TABLE IF NOT EXISTS recensioni (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(80)     NOT NULL,
    valutazione TINYINT         NOT NULL CHECK (valutazione BETWEEN 1 AND 5),
    testo       VARCHAR(400)    NOT NULL,
    ip_address  VARCHAR(45)     DEFAULT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_created      (created_at DESC),
    INDEX idx_valutazione  (valutazione)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabella pronta per le recensioni reali dei clienti
-- =============================================
