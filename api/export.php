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

if ($type !== 'kml' && (empty($selected_cols) || !is_array($selected_cols))) {
    http_response_code(400);
    echo json_encode(['error' => 'Kolom export tidak boleh kosong']);
    exit();
}

function getColLetter($colIdx) {
    $letter = '';
    while ($colIdx >= 0) {
        $letter = chr(($colIdx % 26) + 65) . $letter;
        $colIdx = intval($colIdx / 26) - 1;
    }
    return $letter;
}

function exportToXlsx($filename, $headers, $dataRows) {
    $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
    $zip = new ZipArchive();
    if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Gagal membuat file temporary XLSX");
    }

    // 1. [Content_Types].xml
    $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>';
    $zip->addFromString('[Content_Types].xml', $contentTypes);

    // 2. _rels/.rels
    $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
    $zip->addFromString('_rels/.rels', $rels);

    // 3. xl/workbook.xml
    $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Sheet1" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
    $zip->addFromString('xl/workbook.xml', $workbook);

    // 4. xl/_rels/workbook.xml.rels
    $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>';
    $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);

    // 5. xl/worksheets/sheet1.xml
    $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>';

    $rowIdx = 1;
    
    // Add headers row
    if (!empty($headers)) {
        $sheetXml .= '<row r="' . $rowIdx . '">';
        foreach ($headers as $colIdx => $val) {
            $cellRef = getColLetter($colIdx) . $rowIdx;
            $escapedVal = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
            $sheetXml .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $escapedVal . '</t></is></c>';
        }
        $sheetXml .= '</row>';
        $rowIdx++;
    }

    // Add data rows
    foreach ($dataRows as $rowData) {
        $sheetXml .= '<row r="' . $rowIdx . '">';
        $colIdx = 0;
        foreach ($rowData as $val) {
            $cellRef = getColLetter($colIdx) . $rowIdx;
            if (is_numeric($val) && (strpos($val, '0') !== 0 || strlen($val) === 1)) {
                // Numeric values (exclude leading zeros like phone numbers, ONU codes etc.)
                $sheetXml .= '<c r="' . $cellRef . '"><v>' . $val . '</v></c>';
            } else {
                $escapedVal = htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
                // Remove control characters that are invalid in XML
                $escapedVal = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $escapedVal);
                $sheetXml .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $escapedVal . '</t></is></c>';
            }
            $colIdx++;
        }
        $sheetXml .= '</row>';
        $rowIdx++;
    }

    $sheetXml .= '  </sheetData>
</worksheet>';
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

    $zip->close();

    // Stream download headers
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempFile));
    header('Cache-Control: max-age=0');
    
    readfile($tempFile);
    unlink($tempFile);
    exit();
}

try {
    global $pdo;
    
    $headers = [];
    $dataRows = [];
    
    switch ($type) {
        case 'pelanggan':
            // Setup kolom mapping untuk header & query
            $col_mapping = [
                'nama' => ['header' => 'Nama Pelanggan', 'select' => 'odp_ports.target AS nama_pelanggan'],
                'koordinat' => ['header' => 'Koordinat Pelanggan', 'select' => 'CASE WHEN odp_ports.lat IS NOT NULL AND odp_ports.lng IS NOT NULL THEN CONCAT(odp_ports.lat, \', \', odp_ports.lng) ELSE \'\' END AS koordinat'],
                'odp' => ['header' => 'ODP Terhubung', 'select' => 'odp.name AS odp_name'],
                'port' => ['header' => 'Port ODP', 'select' => 'odp_ports.port_number'],
                'onu' => ['header' => 'Nomor ONU / SN', 'select' => 'odp_ports.onu_number'],
                'modem' => ['header' => 'Jenis Modem / ONT', 'select' => 'odp_ports.modem_type'],
                'keterangan' => ['header' => 'Keterangan Port', 'select' => 'odp_ports.description AS port_desc'],
                'created_at' => ['header' => 'Waktu Dibuat', 'select' => 'odp_ports.created_at'],
                'updated_at' => ['header' => 'Terakhir Diedit', 'select' => 'odp_ports.updated_at']
            ];
            
            // Build query selects
            $select_fields = [];
            
            foreach ($selected_cols as $col) {
                if (isset($col_mapping[$col])) {
                    $headers[] = $col_mapping[$col]['header'];
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
            
            // Output data
            foreach ($rows as $row) {
                $line = [];
                foreach ($selected_cols as $col) {
                    if ($col === 'nama') $line[] = $row['nama_pelanggan'];
                    elseif ($col === 'koordinat') $line[] = $row['koordinat'];
                    elseif ($col === 'odp') $line[] = $row['odp_name'];
                    elseif ($col === 'port') $line[] = $row['port_number'];
                    elseif ($col === 'onu') $line[] = $row['onu_number'];
                    elseif ($col === 'modem') $line[] = $row['modem_type'];
                    elseif ($col === 'keterangan') $line[] = $row['port_desc'];
                    elseif ($col === 'created_at') $line[] = $row['created_at'];
                    elseif ($col === 'updated_at') $line[] = $row['updated_at'];
                }
                $dataRows[] = $line;
            }
            
            exportToXlsx("export_pelanggan_" . date('Ymd_His') . ".xlsx", $headers, $dataRows);
            break;
            
        case 'odp':
            $col_mapping = [
                'nama' => ['header' => 'Nama ODP', 'select' => 'odp.name AS odp_name'],
                'koordinat' => ['header' => 'Koordinat ODP', 'select' => 'CASE WHEN odp.lat IS NOT NULL AND odp.lng IS NOT NULL THEN CONCAT(odp.lat, \', \', odp.lng) ELSE \'\' END AS koordinat'],
                'total_ports' => ['header' => 'Jumlah Port', 'select' => 'odp.total_ports'],
                'port_terpakai' => ['header' => 'Port Terpakai', 'select' => '(SELECT COUNT(*) FROM odp_ports WHERE odp_ports.odp_id = odp.id AND odp_ports.status = \'used\') AS used_ports'],
                'port_tersedia' => ['header' => 'Port Tersedia', 'select' => 'odp.available_ports'],
                'ms_terhubung' => ['header' => 'Terhubung ke MS', 'select' => 'odc.name AS ms_name'],
                'keterangan' => ['header' => 'Keterangan ODP', 'select' => 'odp.description AS odp_desc'],
                'created_at' => ['header' => 'Waktu Dibuat', 'select' => 'odp.created_at'],
                'updated_at' => ['header' => 'Terakhir Diedit', 'select' => 'odp.updated_at']
            ];
            
            $select_fields = [];
            
            foreach ($selected_cols as $col) {
                if (isset($col_mapping[$col])) {
                    $headers[] = $col_mapping[$col]['header'];
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
            
            foreach ($rows as $row) {
                $line = [];
                foreach ($selected_cols as $col) {
                    if ($col === 'nama') $line[] = $row['odp_name'];
                    elseif ($col === 'koordinat') $line[] = $row['koordinat'];
                    elseif ($col === 'total_ports') $line[] = $row['total_ports'];
                    elseif ($col === 'port_terpakai') $line[] = $row['used_ports'];
                    elseif ($col === 'port_tersedia') $line[] = $row['available_ports'];
                    elseif ($col === 'ms_terhubung') $line[] = $row['ms_name'] ?: 'Tidak Terhubung';
                    elseif ($col === 'keterangan') $line[] = $row['odp_desc'];
                    elseif ($col === 'created_at') $line[] = $row['created_at'];
                    elseif ($col === 'updated_at') $line[] = $row['updated_at'];
                }
                $dataRows[] = $line;
            }
            
            exportToXlsx("export_odp_" . date('Ymd_His') . ".xlsx", $headers, $dataRows);
            break;
            
        case 'odc':
            $col_mapping = [
                'nama' => ['header' => 'Nama ODC (MS)', 'select' => 'odc.name AS odc_name'],
                'koordinat' => ['header' => 'Koordinat ODC', 'select' => 'CASE WHEN odc.lat IS NOT NULL AND odc.lng IS NOT NULL THEN CONCAT(odc.lat, \', \', odc.lng) ELSE \'\' END AS koordinat'],
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
                'keterangan' => ['header' => 'Keterangan ODC', 'select' => 'odc.description AS odc_desc'],
                'created_at' => ['header' => 'Waktu Dibuat', 'select' => 'odc.created_at'],
                'updated_at' => ['header' => 'Terakhir Diedit', 'select' => 'odc.updated_at']
            ];
            
            $select_fields = [];
            
            foreach ($selected_cols as $col) {
                if (isset($col_mapping[$col])) {
                    $headers[] = $col_mapping[$col]['header'];
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
            
            foreach ($rows as $row) {
                $line = [];
                foreach ($selected_cols as $col) {
                    if ($col === 'nama') $line[] = $row['odc_name'];
                    elseif ($col === 'koordinat') $line[] = $row['koordinat'];
                    elseif ($col === 'location') $line[] = $row['location'];
                    elseif ($col === 'capacity') $line[] = $row['capacity'];
                    elseif ($col === 'used_ports') $line[] = $row['used_ports'];
                    elseif ($col === 'available_ports') $line[] = $row['avail_ports'];
                    elseif ($col === 'sumber') $line[] = $row['source_name'] ?: 'Tidak Ada';
                    elseif ($col === 'keterangan') $line[] = $row['odc_desc'];
                    elseif ($col === 'created_at') $line[] = $row['created_at'];
                    elseif ($col === 'updated_at') $line[] = $row['updated_at'];
                }
                $dataRows[] = $line;
            }
            
            exportToXlsx("export_odc_" . date('Ymd_His') . ".xlsx", $headers, $dataRows);
            break;
            
        case 'pop':
            $col_mapping = [
                'nama' => ['header' => 'Nama POP', 'select' => 'pop.name AS pop_name'],
                'code' => ['header' => 'Kode POP', 'select' => 'pop.code'],
                'koordinat' => ['header' => 'Koordinat POP', 'select' => 'CASE WHEN pop.lat IS NOT NULL AND pop.lng IS NOT NULL THEN CONCAT(pop.lat, \', \', pop.lng) ELSE \'\' END AS koordinat'],
                'location' => ['header' => 'Lokasi', 'select' => 'pop.location'],
                'address' => ['header' => 'Alamat Lengkap', 'select' => 'pop.address'],
                'keterangan' => ['header' => 'Keterangan POP', 'select' => 'pop.description AS pop_desc'],
                'created_at' => ['header' => 'Waktu Dibuat', 'select' => 'pop.created_at'],
                'updated_at' => ['header' => 'Terakhir Diedit', 'select' => 'pop.updated_at']
            ];
            
            $select_fields = [];
            
            foreach ($selected_cols as $col) {
                if (isset($col_mapping[$col])) {
                    $headers[] = $col_mapping[$col]['header'];
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
            
            foreach ($rows as $row) {
                $line = [];
                foreach ($selected_cols as $col) {
                    if ($col === 'nama') $line[] = $row['pop_name'];
                    elseif ($col === 'code') $line[] = $row['code'];
                    elseif ($col === 'koordinat') $line[] = $row['koordinat'];
                    elseif ($col === 'location') $line[] = $row['location'];
                    elseif ($col === 'address') $line[] = $row['address'];
                    elseif ($col === 'keterangan') $line[] = $row['pop_desc'];
                    elseif ($col === 'created_at') $line[] = $row['created_at'];
                    elseif ($col === 'updated_at') $line[] = $row['updated_at'];
                }
                $dataRows[] = $line;
            }
            
            exportToXlsx("export_pop_" . date('Ymd_His') . ".xlsx", $headers, $dataRows);
            break;
            
        case 'kml':
            $selected_layers = isset($_GET['layers']) ? $_GET['layers'] : [];
            if (empty($selected_layers)) {
                $selected_layers = ['pop', 'odc', 'odp', 'pelanggan'];
            }
            
            // Set headers for KML
            $filename = "fiber_map_" . date('Ymd_His') . ".kml";
            header('Content-Type: application/vnd.google-earth.kml+xml');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
            echo '  <Document>' . "\n";
            echo '    <name>Peta Fiber Optik</name>' . "\n";
            echo '    <description>Exported from Fiber Manager on ' . date('Y-m-d H:i:s') . '</description>' . "\n";
            
            // Styles
            echo '    <Style id="popStyle"><IconStyle><scale>1.1</scale><Icon><href>http://maps.google.com/mapfiles/kml/shapes/info_circle.png</href></Icon></IconStyle></Style>' . "\n";
            echo '    <Style id="odcStyle"><IconStyle><scale>1.1</scale><Icon><href>http://maps.google.com/mapfiles/kml/paddle/orange-stars.png</href></Icon></IconStyle></Style>' . "\n";
            echo '    <Style id="odpStyle"><IconStyle><scale>1.0</scale><Icon><href>http://maps.google.com/mapfiles/kml/paddle/grn-circle.png</href></Icon></IconStyle></Style>' . "\n";
            echo '    <Style id="customerStyle"><IconStyle><scale>0.8</scale><Icon><href>http://maps.google.com/mapfiles/kml/shapes/home.png</href></Icon></IconStyle></Style>' . "\n";
            echo '    <Style id="feederCable"><LineStyle><color>ff0080ff</color><width>4</width></LineStyle></Style>' . "\n"; // Orange
            echo '    <Style id="distCable"><LineStyle><color>ff00ff00</color><width>3</width></LineStyle></Style>' . "\n"; // Green
            echo '    <Style id="dropCable"><LineStyle><color>ff0000ff</color><width>2</width></LineStyle></Style>' . "\n"; // Red
            
            // 1. POP Layer
            if (in_array('pop', $selected_layers)) {
                echo '    <Folder>' . "\n";
                echo '      <name>Point of Presence (POP)</name>' . "\n";
                $stmt = $pdo->query("SELECT * FROM pop ORDER BY name ASC");
                while ($row = $stmt->fetch()) {
                    echo '      <Placemark>' . "\n";
                    echo '        <name>' . htmlspecialchars($row['name']) . '</name>' . "\n";
                    echo '        <description>' . htmlspecialchars("Code: " . $row['code'] . "\nLokasi: " . $row['location'] . "\nAlamat: " . $row['address'] . "\nKet: " . $row['description']) . '</description>' . "\n";
                    echo '        <styleUrl>#popStyle</styleUrl>' . "\n";
                    echo '        <Point>' . "\n";
                    echo '          <coordinates>' . $row['lng'] . ',' . $row['lat'] . ',0</coordinates>' . "\n";
                    echo '        </Point>' . "\n";
                    echo '      </Placemark>' . "\n";
                }
                echo '    </Folder>' . "\n";
            }
            
            // 2. ODC Layer
            if (in_array('odc', $selected_layers)) {
                echo '    <Folder>' . "\n";
                echo '      <name>ODC (MS)</name>' . "\n";
                $stmt = $pdo->query("SELECT * FROM odc ORDER BY name ASC");
                while ($row = $stmt->fetch()) {
                    // Marker point
                    echo '      <Placemark>' . "\n";
                    echo '        <name>' . htmlspecialchars($row['name']) . '</name>' . "\n";
                    echo '        <description>' . htmlspecialchars("Kapasitas: " . $row['capacity'] . "\nPort Terpakai: " . $row['used_ports'] . "\nLokasi: " . $row['location'] . "\nKet: " . $row['description']) . '</description>' . "\n";
                    echo '        <styleUrl>#odcStyle</styleUrl>' . "\n";
                    echo '        <Point>' . "\n";
                    echo '          <coordinates>' . $row['lng'] . ',' . $row['lat'] . ',0</coordinates>' . "\n";
                    echo '        </Point>' . "\n";
                    echo '      </Placemark>' . "\n";
                    
                    // Cable Line (Feeder)
                    if (!empty($row['path_coordinates'])) {
                        $coords = json_decode($row['path_coordinates'], true);
                        if (is_array($coords) && count($coords) > 1) {
                            $kml_coords = [];
                            foreach ($coords as $c) {
                                if (count($c) >= 2) $kml_coords[] = $c[1] . ',' . $c[0] . ',0';
                            }
                            echo '      <Placemark>' . "\n";
                            echo '        <name>Kabel Feeder - ' . htmlspecialchars($row['name']) . '</name>' . "\n";
                            echo '        <styleUrl>#feederCable</styleUrl>' . "\n";
                            echo '        <LineString>' . "\n";
                            echo '          <tessellate>1</tessellate>' . "\n";
                            echo '          <coordinates>' . implode(' ', $kml_coords) . '</coordinates>' . "\n";
                            echo '        </LineString>' . "\n";
                            echo '      </Placemark>' . "\n";
                        }
                    }
                }
                echo '    </Folder>' . "\n";
            }
            
            // 3. ODP Layer
            if (in_array('odp', $selected_layers)) {
                echo '    <Folder>' . "\n";
                echo '      <name>ODP</name>' . "\n";
                $stmt = $pdo->query("SELECT odp.*, odc.name AS odc_name FROM odp LEFT JOIN odc ON odp.source_id = odc.id ORDER BY odp.name ASC");
                while ($row = $stmt->fetch()) {
                    // Marker point
                    echo '      <Placemark>' . "\n";
                    echo '        <name>' . htmlspecialchars($row['name']) . '</name>' . "\n";
                    echo '        <description>' . htmlspecialchars("MS Terhubung: " . ($row['odc_name'] ?: 'Tidak Terhubung') . "\nTotal Port: " . $row['total_ports'] . "\nPort Tersedia: " . $row['available_ports'] . "\nLokasi: " . $row['location'] . "\nKet: " . $row['description']) . '</description>' . "\n";
                    echo '        <styleUrl>#odpStyle</styleUrl>' . "\n";
                    echo '        <Point>' . "\n";
                    echo '          <coordinates>' . $row['lng'] . ',' . $row['lat'] . ',0</coordinates>' . "\n";
                    echo '        </Point>' . "\n";
                    echo '      </Placemark>' . "\n";
                    
                    // Cable Line (Distribution)
                    if (!empty($row['path_coordinates'])) {
                        $coords = json_decode($row['path_coordinates'], true);
                        if (is_array($coords) && count($coords) > 1) {
                            $kml_coords = [];
                            foreach ($coords as $c) {
                                if (count($c) >= 2) $kml_coords[] = $c[1] . ',' . $c[0] . ',0';
                            }
                            echo '      <Placemark>' . "\n";
                            echo '        <name>Kabel Distribusi - ' . htmlspecialchars($row['name']) . '</name>' . "\n";
                            echo '        <styleUrl>#distCable</styleUrl>' . "\n";
                            echo '        <LineString>' . "\n";
                            echo '          <tessellate>1</tessellate>' . "\n";
                            echo '          <coordinates>' . implode(' ', $kml_coords) . '</coordinates>' . "\n";
                            echo '        </LineString>' . "\n";
                            echo '      </Placemark>' . "\n";
                        }
                    }
                }
                echo '    </Folder>' . "\n";
            }
            
            // 4. Pelanggan Layer
            if (in_array('pelanggan', $selected_layers)) {
                echo '    <Folder>' . "\n";
                echo '      <name>Pelanggan &amp; Drop Cable</name>' . "\n";
                $stmt = $pdo->query("SELECT odp_ports.*, odp.name AS odp_name FROM odp_ports JOIN odp ON odp_ports.odp_id = odp.id WHERE odp_ports.status = 'used' AND odp_ports.target IS NOT NULL AND odp_ports.target != ''");
                while ($row = $stmt->fetch()) {
                    // Marker point if coordinates exist
                    if ($row['lat'] && $row['lng']) {
                        echo '      <Placemark>' . "\n";
                        echo '        <name>' . htmlspecialchars($row['target']) . '</name>' . "\n";
                        echo '        <description>' . htmlspecialchars("ODP: " . $row['odp_name'] . " Port: " . $row['port_number'] . "\nONU/SN: " . $row['onu_number'] . "\nModem: " . $row['modem_type'] . "\nKet: " . $row['description']) . '</description>' . "\n";
                        echo '        <styleUrl>#customerStyle</styleUrl>' . "\n";
                        echo '        <Point>' . "\n";
                        echo '          <coordinates>' . $row['lng'] . ',' . $row['lat'] . ',0</coordinates>' . "\n";
                        echo '        </Point>' . "\n";
                        echo '      </Placemark>' . "\n";
                    }
                    
                    // Cable Line (Drop Core)
                    if (!empty($row['path_coordinates'])) {
                        $coords = json_decode($row['path_coordinates'], true);
                        if (is_array($coords) && count($coords) > 1) {
                            $kml_coords = [];
                            foreach ($coords as $c) {
                                if (count($c) >= 2) $kml_coords[] = $c[1] . ',' . $c[0] . ',0';
                            }
                            echo '      <Placemark>' . "\n";
                            echo '        <name>Kabel Drop - ' . htmlspecialchars($row['target']) . '</name>' . "\n";
                            echo '        <styleUrl>#dropCable</styleUrl>' . "\n";
                            echo '        <LineString>' . "\n";
                            echo '          <tessellate>1</tessellate>' . "\n";
                            echo '          <coordinates>' . implode(' ', $kml_coords) . '</coordinates>' . "\n";
                            echo '        </LineString>' . "\n";
                            echo '      </Placemark>' . "\n";
                        }
                    }
                }
                echo '    </Folder>' . "\n";
            }
            
            echo '  </Document>' . "\n";
            echo '</kml>' . "\n";
            exit();
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Terjadi kesalahan database: " . $e->getMessage();
}
?>
