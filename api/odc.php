<?php
require_once 'config.php';

// Proteksi: harus login
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// =============================================
// PERBAIKAN: Switch statement yang benar
// =============================================
switch($method) {
    case 'GET':
        if (isset($_GET['sources'])) {
            getAvailableSources();
        } elseif ($id && isset($_GET['ports'])) {
            getODCPorts($id);
        } elseif ($id) {
            getODC($id);
        } else {
            getAllODC();
        }
        break;
        
    case 'POST':
        checkRole(['admin', 'operator']);
        createODC();
        break;
        
    case 'PUT':
        checkRole(['admin', 'operator']);
        updateODC($id);
        break;
        
    case 'DELETE':
        checkRole(['admin']);
        deleteODC($id);
        break;
        
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}

// =============================================
// FUNGSI-FUNGSI
// =============================================

function getAllODC() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM odc_odp_connections WHERE odc_id = o.id) as connected_odps,
                   CASE 
                       WHEN o.source_type = 'pop' THEN (SELECT name FROM pop WHERE id = o.source_id)
                       WHEN o.source_type = 'olt' THEN (SELECT name FROM olt WHERE id = o.source_id)
                       WHEN o.source_type = 'pon' THEN (SELECT name FROM pon WHERE id = o.source_id)
                       ELSE NULL
                   END as source_name
            FROM odc o 
            ORDER BY o.created_at DESC
        ");
        $odcs = $stmt->fetchAll();
        
        foreach ($odcs as &$odc) {
            $stmt2 = $pdo->prepare("
                SELECT id, filename, original_name, is_primary,
                       CONCAT('uploads/odc/', filename) as url
                FROM odc_photos 
                WHERE odc_id = ? 
                ORDER BY is_primary DESC
            ");
            $stmt2->execute([$odc['id']]);
            $odc['photos'] = $stmt2->fetchAll();
        }
        
        sendResponse($odcs);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function getODC($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM odc_odp_connections WHERE odc_id = o.id) as connected_odps
            FROM odc o 
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $odc = $stmt->fetch();
        
        if ($odc) {
            // Get connected ODPs with port numbers
            $stmt2 = $pdo->prepare("
                SELECT odp.id, odp.name, odp.port_number_in_odc as port_number
                FROM odc_odp_connections coc
                JOIN odp ON coc.odp_id = odp.id
                WHERE coc.odc_id = ?
            ");
            $stmt2->execute([$id]);
            $odc['connected_odps_list'] = $stmt2->fetchAll();
            
            // Update used_ports count
            $odc['used_ports'] = count($odc['connected_odps_list']);
            
            // Get photos
            $stmt3 = $pdo->prepare("
                SELECT id, filename, original_name, is_primary, file_size, created_at,
                       CONCAT('uploads/odc/', filename) as url
                FROM odc_photos 
                WHERE odc_id = ? 
                ORDER BY is_primary DESC, created_at ASC
            ");
            $stmt3->execute([$id]);
            $odc['photos'] = $stmt3->fetchAll();
            
            sendResponse($odc);
        } else {
            sendResponse(['error' => 'ODC not found'], 404);
        }
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function getODCPorts($odc_id) {
    global $pdo;
    try {
        // Get all connections with port numbers
        $stmt = $pdo->prepare("
            SELECT 
                coc.odp_id,
                odp.name as odp_name,
                coc.port_number as port_number
            FROM odc_odp_connections coc
            JOIN odp ON coc.odp_id = odp.id
            WHERE coc.odc_id = ?
        ");
        $stmt->execute([$odc_id]);
        $usedPorts = $stmt->fetchAll();
        
        // Get ODC capacity
        $stmt2 = $pdo->prepare("SELECT capacity FROM odc WHERE id = ?");
        $stmt2->execute([$odc_id]);
        $odc = $stmt2->fetch();
        $capacity = $odc ? $odc['capacity'] : 24;
        
        // Buat array port yang terpakai
        $usedPortMap = [];
        foreach ($usedPorts as $port) {
            $usedPortMap[$port['port_number']] = [
                'odp_id' => $port['odp_id'],
                'odp_name' => $port['odp_name']
            ];
        }
        
        // Generate semua port 1..capacity
        $ports = [];
        for ($i = 1; $i <= $capacity; $i++) {
            if (isset($usedPortMap[$i])) {
                $ports[] = [
                    'port_number' => $i,
                    'status' => 'used',
                    'odp_id' => $usedPortMap[$i]['odp_id'],
                    'odp_name' => $usedPortMap[$i]['odp_name']
                ];
            } else {
                $ports[] = [
                    'port_number' => $i,
                    'status' => 'available',
                    'odp_id' => null,
                    'odp_name' => null
                ];
            }
        }
        
        sendResponse($ports);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function createODC() {
    global $pdo;
    $data = getRequestData();
    
    if (!isset($data['name']) || !isset($data['lat']) || !isset($data['lng'])) {
        sendResponse(['error' => 'Missing required fields: name, lat, lng'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO odc (name, lat, lng, location, capacity, description, source_type, source_id, pon_port_number)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['lat'],
            $data['lng'],
            $data['location'] ?? '',
            $data['capacity'] ?? 24,
            $data['description'] ?? '',
            $data['source_type'] ?? null,
            $data['source_id'] ?? null,
            $data['pon_port_number'] ?? null
        ]);
        
        $id = $pdo->lastInsertId();
        
        // Update used_ports
        updateODCUsedPorts($id);
        
        sendResponse(['id' => $id, 'message' => 'ODC created successfully']);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function updateODC($id) {
    global $pdo;
    if (!$id) {
        sendResponse(['error' => 'ID is required'], 400);
    }
    
    $data = getRequestData();
    
    try {
        $fields = [];
        $values = [];
        
        if (isset($data['name'])) { $fields[] = "name = ?"; $values[] = $data['name']; }
        if (isset($data['lat'])) { $fields[] = "lat = ?"; $values[] = $data['lat']; }
        if (isset($data['lng'])) { $fields[] = "lng = ?"; $values[] = $data['lng']; }
        if (isset($data['location'])) { $fields[] = "location = ?"; $values[] = $data['location']; }
        if (isset($data['capacity'])) { $fields[] = "capacity = ?"; $values[] = $data['capacity']; }
        if (isset($data['description'])) { $fields[] = "description = ?"; $values[] = $data['description']; }
        if (isset($data['source_type'])) { $fields[] = "source_type = ?"; $values[] = $data['source_type']; }
        if (isset($data['source_id'])) { $fields[] = "source_id = ?"; $values[] = $data['source_id']; }
        if (isset($data['pon_port_number'])) { $fields[] = "pon_port_number = ?"; $values[] = $data['pon_port_number']; }
        
        if (empty($fields)) {
            sendResponse(['error' => 'No fields to update'], 400);
        }
        
        $values[] = $id;
        $sql = "UPDATE odc SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        // Update used_ports based on connections
        updateODCUsedPorts($id);
        
        sendResponse(['message' => 'ODC updated successfully']);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function deleteODC($id) {
    global $pdo;
    if (!$id) {
        sendResponse(['error' => 'ID is required'], 400);
    }
    
    try {
        $pdo->beginTransaction();
        
        // Set source_id to NULL for connected ODPs
        $stmt = $pdo->prepare("UPDATE odp SET source_id = NULL, source_type = NULL, port_number_in_odc = NULL WHERE source_id = ? AND source_type = 'odc'");
        $stmt->execute([$id]);
        
        // Delete connections
        $stmt = $pdo->prepare("DELETE FROM odc_odp_connections WHERE odc_id = ?");
        $stmt->execute([$id]);
        
        // Delete ODC
        $stmt = $pdo->prepare("DELETE FROM odc WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        sendResponse(['message' => 'ODC deleted successfully']);
    } catch(PDOException $e) {
        $pdo->rollBack();
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function getAvailableSources() {
    global $pdo;
    try {
        $sources = [
            'pops' => [],
            'olts' => [],
            'pons' => []
        ];
        
        // Get POPs
        $result = $pdo->query("SELECT id, name, code, location FROM pop ORDER BY name");
        $sources['pops'] = $result->fetchAll();
        
        // Get OLTS with POP info
        $result = $pdo->query("
            SELECT o.id, o.name, o.model, p.name as pop_name, p.id as pop_id
            FROM olt o
            JOIN pop p ON o.pop_id = p.id
            ORDER BY p.name, o.name
        ");
        $sources['olts'] = $result->fetchAll();
        
        // Get PONs with OLT and POP info
        $result = $pdo->query("
            SELECT p.id, p.port_number, p.name as pon_name, 
                   o.id as olt_id, o.name as olt_name,
                   po.id as pop_id, po.name as pop_name
            FROM pon p
            JOIN olt o ON p.olt_id = o.id
            JOIN pop po ON o.pop_id = po.id
            WHERE p.status = 'active'
            ORDER BY po.name, o.name, p.port_number
        ");
        $sources['pons'] = $result->fetchAll();
        
        sendResponse($sources);
    } catch(PDOException $e) {
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

function updateODCUsedPorts($odc_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE odc 
        SET used_ports = (SELECT COUNT(*) FROM odc_odp_connections WHERE odc_id = ?)
        WHERE id = ?
    ");
    $stmt->execute([$odc_id, $odc_id]);
}
?>