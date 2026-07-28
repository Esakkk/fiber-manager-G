<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function handle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu'], 401);
        }

        $type = $request->query('type', '');
        $selected_cols = $request->query('columns', []);

        if (empty($type)) {
            return response()->json(['error' => 'Tipe export tidak ditentukan'], 400);
        }

        if ($type !== 'kml' && (empty($selected_cols) || !is_array($selected_cols))) {
            return response()->json(['error' => 'Kolom export tidak boleh kosong'], 400);
        }

        try {
            $headers = [];
            $dataRows = [];

            switch ($type) {
                case 'pelanggan':
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

                    $rows = DB::table('odp_ports')
                        ->leftJoin('odp', 'odp_ports.odp_id', '=', 'odp.id')
                        ->select(DB::raw(implode(', ', $select_fields)))
                        ->where('odp_ports.status', 'used')
                        ->orderBy('odp.name', 'asc')
                        ->orderBy('odp_ports.port_number', 'asc')
                        ->get();

                    foreach ($rows as $row) {
                        $line = [];
                        $rowArray = (array)$row;
                        foreach ($selected_cols as $col) {
                            if ($col === 'nama') $line[] = $rowArray['nama_pelanggan'] ?? '';
                            elseif ($col === 'koordinat') $line[] = $rowArray['koordinat'] ?? '';
                            elseif ($col === 'odp') $line[] = $rowArray['odp_name'] ?? '';
                            elseif ($col === 'port') $line[] = $rowArray['port_number'] ?? '';
                            elseif ($col === 'onu') $line[] = $rowArray['onu_number'] ?? '';
                            elseif ($col === 'modem') $line[] = $rowArray['modem_type'] ?? '';
                            elseif ($col === 'keterangan') $line[] = $rowArray['port_desc'] ?? '';
                            elseif ($col === 'created_at') $line[] = $rowArray['created_at'] ?? '';
                            elseif ($col === 'updated_at') $line[] = $rowArray['updated_at'] ?? '';
                        }
                        $dataRows[] = $line;
                    }

                    return $this->exportToXlsx("export_pelanggan_" . date('Ymd_His') . ".xlsx", $headers, $dataRows);

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

                    $rows = DB::table('odp')
                        ->leftJoin('odc', 'odp.source_id', '=', 'odc.id')
                        ->select(DB::raw(implode(', ', $select_fields)))
                        ->orderBy('odp.name', 'asc')
                        ->get();

                    foreach ($rows as $row) {
                        $line = [];
                        $rowArray = (array)$row;
                        foreach ($selected_cols as $col) {
                            if ($col === 'nama') $line[] = $rowArray['odp_name'] ?? '';
                            elseif ($col === 'koordinat') $line[] = $rowArray['koordinat'] ?? '';
                            elseif ($col === 'total_ports') $line[] = $rowArray['total_ports'] ?? '';
                            elseif ($col === 'port_terpakai') $line[] = $rowArray['used_ports'] ?? '';
                            elseif ($col === 'port_tersedia') $line[] = $rowArray['available_ports'] ?? '';
                            elseif ($col === 'ms_terhubung') $line[] = ($rowArray['ms_name'] ?? '') ?: 'Tidak Terhubung';
                            elseif ($col === 'keterangan') $line[] = $rowArray['odp_desc'] ?? '';
                            elseif ($col === 'created_at') $line[] = $rowArray['created_at'] ?? '';
                            elseif ($col === 'updated_at') $line[] = $rowArray['updated_at'] ?? '';
                        }
                        $dataRows[] = $line;
                    }

                    return $this->exportToXlsx("export_odp_" . date('Ymd_His') . ".xlsx", $headers, $dataRows);

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

                    $rows = DB::table('odc')
                        ->select(DB::raw(implode(', ', $select_fields)))
                        ->orderBy('odc.name', 'asc')
                        ->get();

                    foreach ($rows as $row) {
                        $line = [];
                        $rowArray = (array)$row;
                        foreach ($selected_cols as $col) {
                            if ($col === 'nama') $line[] = $rowArray['odc_name'] ?? '';
                            elseif ($col === 'koordinat') $line[] = $rowArray['koordinat'] ?? '';
                            elseif ($col === 'location') $line[] = $rowArray['location'] ?? '';
                            elseif ($col === 'capacity') $line[] = $rowArray['capacity'] ?? '';
                            elseif ($col === 'used_ports') $line[] = $rowArray['used_ports'] ?? '';
                            elseif ($col === 'available_ports') $line[] = $rowArray['avail_ports'] ?? '';
                            elseif ($col === 'sumber') $line[] = ($rowArray['source_name'] ?? '') ?: 'Tidak Ada';
                            elseif ($col === 'keterangan') $line[] = $rowArray['odc_desc'] ?? '';
                            elseif ($col === 'created_at') $line[] = $rowArray['created_at'] ?? '';
                            elseif ($col === 'updated_at') $line[] = $rowArray['updated_at'] ?? '';
                        }
                        $dataRows[] = $line;
                    }

                    return $this->exportToXlsx("export_odc_" . date('Ymd_His') . ".xlsx", $headers, $dataRows);

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

                    $rows = DB::table('pop')
                        ->select(DB::raw(implode(', ', $select_fields)))
                        ->orderBy('pop.name', 'asc')
                        ->get();

                    foreach ($rows as $row) {
                        $line = [];
                        $rowArray = (array)$row;
                        foreach ($selected_cols as $col) {
                            if ($col === 'nama') $line[] = $rowArray['pop_name'] ?? '';
                            elseif ($col === 'code') $line[] = $rowArray['code'] ?? '';
                            elseif ($col === 'koordinat') $line[] = $rowArray['koordinat'] ?? '';
                            elseif ($col === 'location') $line[] = $rowArray['location'] ?? '';
                            elseif ($col === 'address') $line[] = $rowArray['address'] ?? '';
                            elseif ($col === 'keterangan') $line[] = $rowArray['pop_desc'] ?? '';
                            elseif ($col === 'created_at') $line[] = $rowArray['created_at'] ?? '';
                            elseif ($col === 'updated_at') $line[] = $rowArray['updated_at'] ?? '';
                        }
                        $dataRows[] = $line;
                    }

                    return $this->exportToXlsx("export_pop_" . date('Ymd_His') . ".xlsx", $headers, $dataRows);

                case 'kml':
                    $selected_layers = $request->query('layers', []);
                    if (empty($selected_layers)) {
                        $selected_layers = ['pop', 'odc', 'odp', 'pelanggan'];
                    }

                    $filename = "fiber_map_" . date('Ymd_His') . ".kml";
                    $kml = $this->generateKml($selected_layers);

                    return response($kml)
                        ->header('Content-Type', 'application/vnd.google-earth.kml+xml')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    protected function getColLetter($colIdx)
    {
        $letter = '';
        while ($colIdx >= 0) {
            $letter = chr(($colIdx % 26) + 65) . $letter;
            $colIdx = intval($colIdx / 26) - 1;
        }
        return $letter;
    }

    protected function exportToXlsx($filename, $headers, $dataRows)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new \ZipArchive();
        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Gagal membuat file temporary XLSX");
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
                $cellRef = $this->getColLetter($colIdx) . $rowIdx;
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
                $cellRef = $this->getColLetter($colIdx) . $rowIdx;
                if (is_numeric($val) && (strpos($val, '0') !== 0 || strlen($val) === 1)) {
                    $sheetXml .= '<c r="' . $cellRef . '"><v>' . $val . '</v></c>';
                } else {
                    $escapedVal = htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
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

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ])->deleteFileAfterSend(true);
    }

    protected function generateKml($selected_layers)
    {
        $kml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $kml .= '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        $kml .= '  <Document>' . "\n";
        $kml .= '    <name>Peta Fiber Optik</name>' . "\n";
        $kml .= '    <description>Exported from Fiber Manager on ' . date('Y-m-d H:i:s') . '</description>' . "\n";

        // Styles
        $kml .= '    <Style id="popStyle"><IconStyle><scale>1.1</scale><Icon><href>http://maps.google.com/mapfiles/kml/shapes/info_circle.png</href></Icon></IconStyle></Style>' . "\n";
        $kml .= '    <Style id="odcStyle"><IconStyle><scale>1.1</scale><Icon><href>http://maps.google.com/mapfiles/kml/paddle/orange-stars.png</href></Icon></IconStyle></Style>' . "\n";
        $kml .= '    <Style id="odpStyle"><IconStyle><scale>1.0</scale><Icon><href>http://maps.google.com/mapfiles/kml/paddle/grn-circle.png</href></Icon></IconStyle></Style>' . "\n";
        $kml .= '    <Style id="customerStyle"><IconStyle><scale>0.8</scale><Icon><href>http://maps.google.com/mapfiles/kml/shapes/home.png</href></Icon></IconStyle></Style>' . "\n";
        $kml .= '    <Style id="feederCable"><LineStyle><color>ff0080ff</color><width>4</width></LineStyle></Style>' . "\n";
        $kml .= '    <Style id="distCable"><LineStyle><color>ff00ff00</color><width>3</width></LineStyle></Style>' . "\n";
        $kml .= '    <Style id="dropCable"><LineStyle><color>ff0000ff</color><width>2</width></LineStyle></Style>' . "\n";

        // 1. POP Layer
        if (in_array('pop', $selected_layers)) {
            $kml .= '    <Folder>' . "\n";
            $kml .= '      <name>Point of Presence (POP)</name>' . "\n";
            $pops = DB::table('pop')->orderBy('name', 'asc')->get();
            foreach ($pops as $row) {
                $kml .= '      <Placemark>' . "\n";
                $kml .= '        <name>' . htmlspecialchars($row->name) . '</name>' . "\n";
                $kml .= '        <description>' . htmlspecialchars("Code: " . $row->code . "\nLokasi: " . $row->location . "\nAlamat: " . $row->address . "\nKet: " . $row->description) . '</description>' . "\n";
                $kml .= '        <styleUrl>#popStyle</styleUrl>' . "\n";
                $kml .= '        <Point>' . "\n";
                $kml .= '          <coordinates>' . $row->lng . ',' . $row->lat . ',0</coordinates>' . "\n";
                $kml .= '        </Point>' . "\n";
                $kml .= '      </Placemark>' . "\n";
            }
            $kml .= '    </Folder>' . "\n";
        }

        // 2. ODC Layer
        if (in_array('odc', $selected_layers)) {
            $kml .= '    <Folder>' . "\n";
            $kml .= '      <name>ODC (MS)</name>' . "\n";
            $odcs = DB::table('odc')->orderBy('name', 'asc')->get();
            foreach ($odcs as $row) {
                $kml .= '      <Placemark>' . "\n";
                $kml .= '        <name>' . htmlspecialchars($row->name) . '</name>' . "\n";
                $kml .= '        <description>' . htmlspecialchars("Kapasitas: " . $row->capacity . "\nPort Terpakai: " . $row->used_ports . "\nLokasi: " . $row->location . "\nKet: " . $row->description) . '</description>' . "\n";
                $kml .= '        <styleUrl>#odcStyle</styleUrl>' . "\n";
                $kml .= '        <Point>' . "\n";
                $kml .= '          <coordinates>' . $row->lng . ',' . $row->lat . ',0</coordinates>' . "\n";
                $kml .= '        </Point>' . "\n";
                $kml .= '      </Placemark>' . "\n";

                // Cable Line (Feeder)
                if (!empty($row->path_coordinates)) {
                    $coords = json_decode($row->path_coordinates, true);
                    if (is_array($coords) && count($coords) > 1) {
                        $kml_coords = [];
                        foreach ($coords as $c) {
                            if (count($c) >= 2) $kml_coords[] = $c[1] . ',' . $c[0] . ',0';
                        }
                        $kml .= '      <Placemark>' . "\n";
                        $kml .= '        <name>Kabel Feeder - ' . htmlspecialchars($row->name) . '</name>' . "\n";
                        $kml .= '        <styleUrl>#feederCable</styleUrl>' . "\n";
                        $kml .= '        <LineString>' . "\n";
                        $kml .= '          <tessellate>1</tessellate>' . "\n";
                        $kml .= '          <coordinates>' . implode(' ', $kml_coords) . '</coordinates>' . "\n";
                        $kml .= '        </LineString>' . "\n";
                        $kml .= '      </Placemark>' . "\n";
                    }
                }
            }
            $kml .= '    </Folder>' . "\n";
        }

        // 3. ODP Layer
        if (in_array('odp', $selected_layers)) {
            $kml .= '    <Folder>' . "\n";
            $kml .= '      <name>ODP</name>' . "\n";
            $odps = DB::table('odp')
                ->leftJoin('odc', 'odp.source_id', '=', 'odc.id')
                ->select('odp.*', 'odc.name AS odc_name')
                ->orderBy('odp.name', 'asc')
                ->get();

            foreach ($odps as $row) {
                $kml .= '      <Placemark>' . "\n";
                $kml .= '        <name>' . htmlspecialchars($row->name) . '</name>' . "\n";
                $kml .= '        <description>' . htmlspecialchars("MS Terhubung: " . ($row->odc_name ?: 'Tidak Terhubung') . "\nTotal Port: " . $row->total_ports . "\nPort Tersedia: " . $row->available_ports . "\nLokasi: " . $row->location . "\nKet: " . $row->description) . '</description>' . "\n";
                $kml .= '        <styleUrl>#odpStyle</styleUrl>' . "\n";
                $kml .= '        <Point>' . "\n";
                $kml .= '          <coordinates>' . $row->lng . ',' . $row->lat . ',0</coordinates>' . "\n";
                $kml .= '        </Point>' . "\n";
                $kml .= '      </Placemark>' . "\n";

                // Cable Line (Distribution)
                if (!empty($row->path_coordinates)) {
                    $coords = json_decode($row->path_coordinates, true);
                    if (is_array($coords) && count($coords) > 1) {
                        $kml_coords = [];
                        foreach ($coords as $c) {
                            if (count($c) >= 2) $kml_coords[] = $c[1] . ',' . $c[0] . ',0';
                        }
                        $kml .= '      <Placemark>' . "\n";
                        $kml .= '        <name>Kabel Distribusi - ' . htmlspecialchars($row->name) . '</name>' . "\n";
                        $kml .= '        <styleUrl>#distCable</styleUrl>' . "\n";
                        $kml .= '        <LineString>' . "\n";
                        $kml .= '          <tessellate>1</tessellate>' . "\n";
                        $kml .= '          <coordinates>' . implode(' ', $kml_coords) . '</coordinates>' . "\n";
                        $kml .= '        </LineString>' . "\n";
                        $kml .= '      </Placemark>' . "\n";
                    }
                }
            }
            $kml .= '    </Folder>' . "\n";
        }

        // 4. Pelanggan Layer
        if (in_array('pelanggan', $selected_layers)) {
            $kml .= '    <Folder>' . "\n";
            $kml .= '      <name>Pelanggan &amp; Drop Cable</name>' . "\n";
            $ports = DB::table('odp_ports')
                ->join('odp', 'odp_ports.odp_id', '=', 'odp.id')
                ->select('odp_ports.*', 'odp.name AS odp_name')
                ->where('odp_ports.status', 'used')
                ->whereNotNull('odp_ports.target')
                ->where('odp_ports.target', '!=', '')
                ->get();

            foreach ($ports as $row) {
                // Marker point if coordinates exist
                if ($row->lat && $row->lng) {
                    $kml .= '      <Placemark>' . "\n";
                    $kml .= '        <name>' . htmlspecialchars($row->target) . '</name>' . "\n";
                    $kml .= '        <description>' . htmlspecialchars("ODP: " . $row->odp_name . " Port: " . $row->port_number . "\nONU/SN: " . $row->onu_number . "\nModem: " . $row->modem_type . "\nKet: " . $row->description) . '</description>' . "\n";
                    $kml .= '        <styleUrl>#customerStyle</styleUrl>' . "\n";
                    $kml .= '        <Point>' . "\n";
                    $kml .= '          <coordinates>' . $row->lng . ',' . $row->lat . ',0</coordinates>' . "\n";
                    $kml .= '        </Point>' . "\n";
                    $kml .= '      </Placemark>' . "\n";
                }

                // Cable Line (Drop Core)
                if (!empty($row->path_coordinates)) {
                    $coords = json_decode($row->path_coordinates, true);
                    if (is_array($coords) && count($coords) > 1) {
                        $kml_coords = [];
                        foreach ($coords as $c) {
                            if (count($c) >= 2) $kml_coords[] = $c[1] . ',' . $c[0] . ',0';
                        }
                        $kml .= '      <Placemark>' . "\n";
                        $kml .= '        <name>Kabel Drop - ' . htmlspecialchars($row->target) . '</name>' . "\n";
                        $kml .= '        <styleUrl>#dropCable</styleUrl>' . "\n";
                        $kml .= '        <LineString>' . "\n";
                        $kml .= '          <tessellate>1</tessellate>' . "\n";
                        $kml .= '          <coordinates>' . implode(' ', $kml_coords) . '</coordinates>' . "\n";
                        $kml .= '        </LineString>' . "\n";
                        $kml .= '      </Placemark>' . "\n";
                    }
                }
            }
            $kml .= '    </Folder>' . "\n";
        }

        $kml .= '  </Document>' . "\n";
        $kml .= '</kml>' . "\n";

        return $kml;
    }
}
