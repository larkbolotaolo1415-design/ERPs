<?php
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit();
}

$q = trim($_GET['q'] ?? '');
$email = trim($_GET['email'] ?? '');
$limit = isset($_GET['limit']) ? max(1, min(20, (int)$_GET['limit'])) : 8;

try {
    if ($email !== '') {
        $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            echo json_encode([
                'exists' => true,
                'user' => [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email']
                ]
            ]);
        } else {
            echo json_encode(['exists' => false]);
        }
        exit();
    }

    if ($q === '') {
        echo json_encode(['results' => []]);
        exit();
    }

    $searchTerm = '%' . $q . '%';
    $stmt = $pdo->prepare("
        SELECT id, name, email
        FROM users
        WHERE email LIKE ? OR name LIKE ?
        ORDER BY email ASC
        LIMIT $limit
    ");
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = array_map(function($row) {
        return [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'email' => $row['email']
        ];
    }, $stmt->fetchAll());

    echo json_encode(['results' => $results]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lookup failed']);
}
?>

