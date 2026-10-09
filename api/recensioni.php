<?php
/**
 * Antonio Farella Hair Designer Studio
 * API Recensioni (connessione diretta al Database)
 *
 * GET  → restituisce le recensioni salvate nel database
 * POST → salva immediatamente una nuova recensione nel database
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Connessione al database non riuscita.']);
    exit;
}

// -------------------------------------------------------------
// GET: Leggi tutte le recensioni
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT id, nome, valutazione, testo, created_at FROM recensioni ORDER BY created_at DESC LIMIT 50');
    $recensioni = $stmt->fetchAll();

    $stats = $pdo->query('SELECT COUNT(*) AS totale, ROUND(AVG(valutazione), 1) AS media FROM recensioni')->fetch();

    echo json_encode([
        'success'    => true,
        'recensioni' => $recensioni,
        'stats'      => [
            'totale' => (int)($stats['totale'] ?? 0),
            'media'  => (float)($stats['media'] ?? 5.0),
        ]
    ]);
    exit;
}

// -------------------------------------------------------------
// POST: Salva direttamente la nuova recensione
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Azione di eliminazione protetta da password
    if (isset($input['action']) && $input['action'] === 'delete') {
        if (($input['password'] ?? '') !== '201022') {
            http_response_code(403);
            echo json_encode(['error' => 'Password non valida.']);
            exit;
        }
        $delId = (int)($input['id'] ?? 0);
        if ($delId > 0) {
            $pdo->prepare('DELETE FROM recensioni WHERE id = ?')->execute([$delId]);
            echo json_encode(['success' => true, 'message' => 'Recensione eliminata.']);
            exit;
        }
    }

    $nome        = trim($input['name'] ?? '');
    $valutazione = (int)($input['rating'] ?? 5);
    $testo       = trim($input['text'] ?? '');
    $ip          = $_SERVER['REMOTE_ADDR'] ?? null;

    if ($nome === '' || $testo === '' || $valutazione < 1 || $valutazione > 5) {
        http_response_code(400);
        echo json_encode(['error' => 'Tutti i campi sono obbligatori.']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO recensioni (nome, valutazione, testo, ip_address) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        mb_substr($nome, 0, 80),
        $valutazione,
        mb_substr($testo, 0, 400),
        $ip
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Recensione pubblicata con successo!'
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Metodo non supportato.']);
