<?php
require_once 'config.php';

requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch($method) {
    case 'GET':
        if ($id) {
            getPON($id);
        } else {
            getAllPON();
        }
        break;
    case 'PUT':
        checkRole(['admin', 'operator']);
        updatePON($id);
        break;
    case 'DELETE':
        checkRole(['admin']);
        deletePON($id);
        break;
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}

function getAllPON() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT p.*, o.name as olt_name, o.pop_id, po.name as pop_name
            FROM pon p
            JOIN olt o ON p.olt_id = o.id
            JOIN pop po ON o.pop_id = po.id
            ORDER BY p.created_at DESC
        ");
        sendResponse($stmt->fetchAll());
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function getPON($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, o.name as olt_name, o.pop_id, po.name as pop_name
            FROM pon p
            JOIN olt o ON p.olt_id = o.id
            JOIN pop po ON o.pop_id = po.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $pon = $stmt->fetch();
        
        if ($pon) {
            sendResponse($pon);
        } else {
            sendResponse(['error' => 'PON not found'], 404);
        }
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function updatePON($id) {
    global $pdo;
    if (!$id) sendResponse(['error' => 'ID is required'], 400);
    
    $data = getRequestData();
    
    try {
        $fields = []; $values = [];
        if (isset($data['name'])) { $fields[] = "name = ?"; $values[] = $data['name']; }
        if (isset($data['status'])) { $fields[] = "status = ?"; $values[] = $data['status']; }
        if (isset($data['description'])) { $fields[] = "description = ?"; $values[] = $data['description']; }
        
        if (empty($fields)) {
            sendResponse(['error' => 'No fields to update'], 400);
        }
        
        $values[] = $id;
        $sql = "UPDATE pon SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        sendResponse(['message' => 'PON updated successfully']);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function deletePON($id) {
    global $pdo;
    if (!$id) sendResponse(['error' => 'ID is required'], 400);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM pon WHERE id = ?");
        $stmt->execute([$id]);
        sendResponse(['message' => 'PON deleted successfully']);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}
?>