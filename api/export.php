<?php
require_once 'config.php';

// Proteksi: harus login
requireAuth();

// Ambil input
$type = isset($_GET['type']) ? $_GET['type'] : '';
$selected_cols = isset($_GET['columns']) ? $_GET['columns'] : [];

if (empty($type)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipe export tidak ditentukan']);
    exit();
}

if (empty($selected_cols) || !is_array($selected_cols)) {
    http_response_code(400);
    echo json_encode(['error' => 'Kolom export tidak boleh kosong']);
    exit();
}

// Konfigurasi header download
$filename = "export_" . $type . "_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Tambahkan UTF-8 BOM untuk kompatibilitas Excel
echo "\xEF\xBB\xBF";
// Tentukan pemisah kolom titik koma agar Excel langsung memformat dengan benar di Windows
echo "sep=;\n";

// Helper untuk format teks CSV (escape semicolon dan double quote)
function formatCsvCell($val) {
    if ($val === null) return '';
    $val = str_replace('"', '""', $val);
    if (strpos($val, ';') !== false || strpos($val, '"') !== false || strpos($val, "\n") !== false || strpos($val, "\r") !== false) {
        return '"' . $val . '"';
    }
    return $val;
}

try {
    global $pdo;
    
    switch ($type) {
        case 'pelanggan':
            // Setup kolom mapping untuk header & query
            $col_mapping = [
                'nama' => ['header' => 'Nama Pelanggan', 'select' => 'odp_ports.target AS nama_pelanggan'],
                'koordinat' => ['header' => 'Koordinat Pelanggan', 'select' => 'odp_ports.lat AS customer_lat, odp_ports.lng AS customer_lng'],
                'odp' => ['header' => 'ODP Terhubung', 'select' => 'odp.name AS odp_name'],
                'port' => ['header' => 'Port ODP', 'select' => 'odp_ports.port_number'],
                'onu' => ['header' => 'Nomor ONU / SN', 'select' => 'odp_ports.onu_number'],
                'modem' => ['header' => 'Jenis Modem / ONT', 'select' => 'odp_ports.modem_type'],
                'keterangan' => ['header' => 'Keterangan Port', 'select' => 'odp_ports.description AS port_desc']
            ];
            
            // Build query selects
            $select_fields = [];
            $headers = [];
            
            foreach ($selected_cols as $col) {
                if (isset($col_mapping[$col])) {
                    if ($col === 'koordinat') {
                        $headers[] = 'Latitude Pelanggan';
                        $headers[] = 'Longitude Pelanggan';
                    } else {
                        $headers[] = $col_mapping[$col]['header'];
                    }
                    $select_fields[] = $col_mapping[$col]['select'];
                }
            }
            
            if (empty($select_fields)) {
                $select_fields[] = "odp_ports.target AS nama_pelanggan";
                $headers[] = "Nama Pelanggan";
            }
            
            $select_str = implode(', ', $select_fields);
            $query = "SELECT $select_str FROM odp_ports 
                      LEFT JOIN odp ON odp_ports.odp_id = odp.id 
                      WHERE odp_ports.status = 'used' 
                      ORDER BY odp.name ASC, odp_ports.port_number ASC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            
            // Output headers
            echo implode(';', array_map('formatCsvCell', $headers)) . "\n";
            
            // Output data
            foreach ($rows as $row) {
                $line = [];
                foreach ($selected_cols as $col) {
                    if ($col === 'nama') $line[] = $row['nama_pelanggan'];
                    elseif ($col === 'koordinat') {
                        $line[] = $row['customer_lat'];
                        $line[] = $row['customer_lng'];
                    }
                    elseif ($col === 'odp') $line[] = $row['odp_name'];
                    elseif ($col === 'port') $line[] = $row['port_number'];
                    elseif ($col === 'onu') $line[] = $row['onu_number'];
                    elseif ($col === 'modem') $line[] = $row['modem_type'];
                    elseif ($col === 'keterangan') $line[] = $row['port_desc'];
                }
                echo implode(';', array_map('formatCsvCell', $line)) . "\n";
            }
            break;
            
        case 'odp':
            $col_mapping = [
                'nama' => ['header' => 'Nama ODP', 'select' => 'odp.name AS odp_name'],
                'koordinat' => ['header' => 'Koordinat ODP', 'select' => 'odp.lat, odp.lng'],
                'total_ports' => ['header' => 'Jumlah Port', 'select' => 'odp.total_ports'],
                'port_terpakai' => ['header' => 'Port Terpakai', 'select' => '(SELECT COUNT(*) FROM odp_ports WHERE odp_ports.odp_id = odp.id AND odp_ports.status = \'used\') AS used_ports'],
                'port_tersedia' => ['header' => 'Port Tersedia', 'select' => 'odp.available_ports'],
                'ms_terhubung' => ['header' => 'Terhubung ke MS', 'select' => 'odc.name AS ms_name'],
                'keterangan' => ['header' => 'Keterangan ODP', 'select' => 'odp.description AS odp_desc']
            ];
            
            $select_fields = [];
            $headers = [];
            
            foreach ($selected_cols as $col) {
                if (isset($col_mapping[$col])) {
                    if ($col === 'koordinat') {
                        $headers[] = 'Latitude ODP';
                        $headers[] = 'Longitude ODP';
                    } else {
                        $headers[] = $col_mapping[$col]['header'];
                    }
                    $select_fields[] = $col_mapping[$col]['select'];
                }
            }
            
            if (empty($select_fields)) {
                $select_fields[] = "odp.name AS odp_name";
                $headers[] = "Nama ODP";
            }
            
            $select_str = implode(', ', $select_fields);
            $query = "SELECT $select_str FROM odp 
                      LEFT JOIN odc ON odp.source_id = odc.id 
                      ORDER BY odp.name ASC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            
            echo implode(';', array_map('formatCsvCell', $headers)) . "\n";
            
            foreach ($rows as $row) {
                $line = [];
                foreach ($selected_cols as $col) {
                    if ($col === 'nama') $line[] = $row['odp_name'];
                    elseif ($col === 'koordinat') {
                        $line[] = $row['lat'];
                        $line[] = $row['lng'];
                    }
                    elseif ($col === 'total_ports') $line[] = $row['total_ports'];
                    elseif ($col === 'port_terpakai') $line[] = $row['used_ports'];
                    elseif ($col === 'port_tersedia') $line[] = $row['available_ports'];
                    elseif ($col === 'ms_terhubung') $line[] = $row['ms_name'] ?: 'Tidak Terhubung';
                    elseif ($col === 'keterangan') $line[] = $row['odp_desc'];
                }
                echo implode(';', array_map('formatCsvCell', $line)) . "\n";
            }
            break;
            
        case 'odc':
            $col_mapping = [
                'nama' => ['header' => 'Nama ODC (MS)', 'select' => 'odc.name AS odc_name'],
                'koordinat' => ['header' => 'Koordinat ODC', 'select' => 'odc.lat, odc.lng'],
                'location' => ['header' => 'Lokasi', 'select' => 'odc.location'],
                'capacity' => ['header' => 'Kapasitas Port', 'select' => 'odc.capacity'],
                'used_ports' => ['header' => 'Port Terpakai', 'select' => 'odc.used_ports'],
                'available_ports' => ['header' => 'Port Tersedia', 'select' => '(odc.capacity - odc.used_ports) AS avail_ports'],
                'sumber' => ['header' => 'Sumber Input', 'select' => '
                    CASE 
                        WHEN odc.source_type = \'pop\' THEN (SELECT name FROM pop WHERE pop.id = odc.source_id)
                        WHEN odc.source_type = \'olt\' THEN (SELECT name FROM olt WHERE olt.id = odc.olt_id)
                        WHEN odc.source_type = \'pon\' THEN (SELECT CONCAT(\'PON Card \', pon.card_number, \' (\', olt.name, \')\') FROM pon JOIN olt ON pon.olt_id = olt.id WHERE pon.id = odc.pon_id)
                        ELSE \'Tidak Ada\'
                    END AS source_name'],
                'keterangan' => ['header' => 'Keterangan ODC', 'select' => 'odc.description AS odc_desc']
            ];
            
            $select_fields = [];
            $headers = [];
            
            foreach ($selected_cols as $col) {
                if (isset($col_mapping[$col])) {
                    if ($col === 'koordinat') {
                        $headers[] = 'Latitude ODC';
                        $headers[] = 'Longitude ODC';
                    } else {
                        $headers[] = $col_mapping[$col]['header'];
                    }
                    $select_fields[] = $col_mapping[$col]['select'];
                }
            }
            
            if (empty($select_fields)) {
                $select_fields[] = "odc.name AS odc_name";
                $headers[] = "Nama ODC (MS)";
            }
            
            $select_str = implode(', ', $select_fields);
            $query = "SELECT $select_str FROM odc ORDER BY odc.name ASC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            
            echo implode(';', array_map('formatCsvCell', $headers)) . "\n";
            
            foreach ($rows as $row) {
                $line = [];
                foreach ($selected_cols as $col) {
                    if ($col === 'nama') $line[] = $row['odc_name'];
                    elseif ($col === 'koordinat') {
                        $line[] = $row['lat'];
                        $line[] = $row['lng'];
                    }
                    elseif ($col === 'location') $line[] = $row['location'];
                    elseif ($col === 'capacity') $line[] = $row['capacity'];
                    elseif ($col === 'used_ports') $line[] = $row['used_ports'];
                    elseif ($col === 'available_ports') $line[] = $row['avail_ports'];
                    elseif ($col === 'sumber') $line[] = $row['source_name'] ?: 'Tidak Ada';
                    elseif ($col === 'keterangan') $line[] = $row['odc_desc'];
                }
                echo implode(';', array_map('formatCsvCell', $line)) . "\n";
            }
            break;
            
        case 'pop':
            $col_mapping = [
                'nama' => ['header' => 'Nama POP', 'select' => 'pop.name AS pop_name'],
                'code' => ['header' => 'Kode POP', 'select' => 'pop.code'],
                'koordinat' => ['header' => 'Koordinat POP', 'select' => 'pop.lat, pop.lng'],
                'location' => ['header' => 'Lokasi', 'select' => 'pop.location'],
                'address' => ['header' => 'Alamat Lengkap', 'select' => 'pop.address'],
                'keterangan' => ['header' => 'Keterangan POP', 'select' => 'pop.description AS pop_desc']
            ];
            
            $select_fields = [];
            $headers = [];
            
            foreach ($selected_cols as $col) {
                if (isset($col_mapping[$col])) {
                    if ($col === 'koordinat') {
                        $headers[] = 'Latitude POP';
                        $headers[] = 'Longitude POP';
                    } else {
                        $headers[] = $col_mapping[$col]['header'];
                    }
                    $select_fields[] = $col_mapping[$col]['select'];
                }
            }
            
            if (empty($select_fields)) {
                $select_fields[] = "pop.name AS pop_name";
                $headers[] = "Nama POP";
            }
            
            $select_str = implode(', ', $select_fields);
            $query = "SELECT $select_str FROM pop ORDER BY pop.name ASC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            
            echo implode(';', array_map('formatCsvCell', $headers)) . "\n";
            
            foreach ($rows as $row) {
                $line = [];
                foreach ($selected_cols as $col) {
                    if ($col === 'nama') $line[] = $row['pop_name'];
                    elseif ($col === 'code') $line[] = $row['code'];
                    elseif ($col === 'koordinat') {
                        $line[] = $row['lat'];
                        $line[] = $row['lng'];
                    }
                    elseif ($col === 'location') $line[] = $row['location'];
                    elseif ($col === 'address') $line[] = $row['address'];
                    elseif ($col === 'keterangan') $line[] = $row['pop_desc'];
                }
                echo implode(';', array_map('formatCsvCell', $line)) . "\n";
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Tipe export tidak valid']);
            exit();
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Terjadi kesalahan database: " . $e->getMessage();
}
?>
