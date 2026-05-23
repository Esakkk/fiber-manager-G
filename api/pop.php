<?php
require_once 'config.php';

requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($method) {
    case 'GET':
        if ($id && $action === 'olts') {
            getPOPOLTs($id);
        } elseif ($id) {
            getPOP($id);
        } else {
            getAllPOP();
        }
        break;
    case 'POST':
        checkRole(['admin', 'operator']);
        createPOP();
        break;
    case 'PUT':
        checkRole(['admin', 'operator']);
        updatePOP($id);
        break;
    case 'DELETE':
        checkRole(['admin']);
        deletePOP($id);
        break;
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}

function getAllPOP() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM olt WHERE pop_id = p.id) as olt_count
            FROM pop p 
            ORDER BY p.created_at DESC
        ");
        $pops = $stmt->fetchAll();
        
        foreach ($pops as &$pop) {
            $stmt2 = $pdo->prepare("
                SELECT id, filename, original_name, is_primary,
                       CONCAT('uploads/pop/', filename) as url
                FROM pop_photos 
                WHERE pop_id = ? 
                ORDER BY is_primary DESC
            ");
            $stmt2->execute([$pop['id']]);
            $pop['photos'] = $stmt2->fetchAll();
        }
        
        sendResponse($pops);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function getPOP($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM pop WHERE id = ?");
        $stmt->execute([$id]);
        $pop = $stmt->fetch();
        
        if ($pop) {
            $stmt2 = $pdo->prepare("
                SELECT id, name, model, ip_address, total_pon_ports, used_pon_ports
                FROM olt WHERE pop_id = ? ORDER BY name
            ");
            $stmt2->execute([$id]);
            $pop['olts'] = $stmt2->fetchAll();
            
            $stmt3 = $pdo->prepare("
                SELECT id, filename, original_name, is_primary,
                       CONCAT('uploads/pop/', filename) as url
                FROM pop_photos WHERE pop_id = ? ORDER BY is_primary DESC
            ");
            $stmt3->execute([$id]);
            $pop['photos'] = $stmt3->fetchAll();
            
            sendResponse($pop);
        } else {
            sendResponse(['error' => 'POP not found'], 404);
        }
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function getPOPOLTs($pop_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT o.*,
                   (SELECT COUNT(*) FROM pon WHERE olt_id = o.id) as pon_count
            FROM olt o
            WHERE o.pop_id = ?
            ORDER BY o.name
        ");
        $stmt->execute([$pop_id]);
        $olts = $stmt->fetchAll();
        
        foreach ($olts as &$olt) {
            $stmt2 = $pdo->prepare("
                SELECT id, port_number, name, status
                FROM pon WHERE olt_id = ? ORDER BY port_number
            ");
            $stmt2->execute([$olt['id']]);
            $olt['pons'] = $stmt2->fetchAll();
        }
        
        sendResponse($olts);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function createPOP() {
    global $pdo;
    $data = getRequestData();
    
    if (!isset($data['name']) || !isset($data['lat']) || !isset($data['lng'])) {
        sendResponse(['error' => 'Missing required fields'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO pop (name, code, lat, lng, location, address, description)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['code'] ?? null,
            $data['lat'],
            $data['lng'],
            $data['location'] ?? '',
            $data['address'] ?? '',
            $data['description'] ?? ''
        ]);
        
        sendResponse(['id' => $pdo->lastInsertId(), 'message' => 'POP created successfully']);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function updatePOP($id) {
    global $pdo;
    if (!$id) sendResponse(['error' => 'ID is required'], 400);
    
    $data = getRequestData();
    
    try {
        $fields = []; $values = [];
        $allowed = ['name', 'code', 'lat', 'lng', 'location', 'address', 'description'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            sendResponse(['error' => 'No fields to update'], 400);
        }
        
        $values[] = $id;
        $sql = "UPDATE pop SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        sendResponse(['message' => 'POP updated successfully']);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function deletePOP($id) {
    global $pdo;
    if (!$id) sendResponse(['error' => 'ID is required'], 400);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM pop WHERE id = ?");
        $stmt->execute([$id]);
        sendResponse(['message' => 'POP deleted successfully']);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}
?>