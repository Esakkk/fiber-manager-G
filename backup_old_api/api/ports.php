<?php
require_once 'config.php';

// Proteksi: harus login
requireAuth();

// Konfigurasi port: hanya admin dan operator
checkRole(['admin', 'operator']);

$method = $_SERVER['REQUEST_METHOD'];
$odp_id = isset($_GET['odp_id']) ? (int)$_GET['odp_id'] : null;
$port_number = isset($_GET['port']) ? (int)$_GET['port'] : null;

switch($method) {
    case 'PUT':
        updatePort($odp_id, $port_number);
        break;
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}

function updatePort($odp_id, $port_number) {
    global $pdo;
    
    if (!$odp_id || !$port_number) {
        sendResponse(['error' => 'ODP ID and port number are required'], 400);
    }
    
    $data = getRequestData();
    
    try {
        $fields = [];
        $values = [];
        
        // Only update fields that are provided
        if (isset($data['status'])) { $fields[] = "status = ?"; $values[] = $data['status']; }
        if (isset($data['target'])) { $fields[] = "target = ?"; $values[] = $data['target']; }
        if (isset($data['connection_type'])) { $fields[] = "connection_type = ?"; $values[] = $data['connection_type']; }
        if (isset($data['target_port'])) { $fields[] = "target_port = ?"; $values[] = $data['target_port']; }
        if (isset($data['lat'])) { $fields[] = "lat = ?"; $values[] = $data['lat']; }
        if (isset($data['lng'])) { $fields[] = "lng = ?"; $values[] = $data['lng']; }
        if (isset($data['onu_number'])) { $fields[] = "onu_number = ?"; $values[] = $data['onu_number']; }
        if (isset($data['modem_type'])) { $fields[] = "modem_type = ?"; $values[] = $data['modem_type']; }
        if (isset($data['description'])) { $fields[] = "description = ?"; $values[] = $data['description']; }
        if (isset($data['path_coordinates'])) { $fields[] = "path_coordinates = ?"; $values[] = $data['path_coordinates']; }
        
        if (empty($fields)) {
            sendResponse(['error' => 'No fields to update'], 400);
            return;
        }
        
        $values[] = $odp_id;
        $values[] = $port_number;
        
        $fieldString = implode(', ', $fields);
        $sql = "UPDATE odp_ports SET $fieldString WHERE odp_id = ? AND port_number = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        // Update available_ports in ODP table only if status changed
        if (isset($data['status'])) {
            $stmt2 = $pdo->prepare("
                UPDATE odp 
                SET available_ports = (
                    SELECT COUNT(*) FROM odp_ports 
                    WHERE odp_id = ? AND status = 'available'
                )
                WHERE id = ?
            ");
            $stmt2->execute([$odp_id, $odp_id]);
        }
        
        sendResponse(['message' => 'Port updated successfully']);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}
?>