<?php
// require_once 'core/config.php';
require_once dirname(__DIR__) . '/core/config.php';
// require_once dirname(__DIR__) . '/includes/header.php';


header('Content-Type: application/json');

$parent_id = isset($_GET['parent_id']) && $_GET['parent_id'] !== '' ? (int)$_GET['parent_id'] : null;
$sector_id = isset($_GET['sector_id']) && $_GET['sector_id'] !== '' ? (int)$_GET['sector_id'] : null;
$type = isset($_GET['type']) ? clean_input($conn, $_GET['type']) : 'province';
$search = isset($_GET['search']) ? clean_input($conn, $_GET['search']) : '';
$sector_name = isset($_GET['sector']) ? clean_input($conn, $_GET['sector']) : '';

// 1. Street Query
if ($type === 'street') {
    if (!empty($search)) {
        $sql = "SELECT id, code, name, district, district_id, sector, sector_id, area, fee AS delivery_fee 
                FROM kigali_streets 
                WHERE code LIKE '%$search%' OR name LIKE '%$search%' OR sector LIKE '%$search%' OR area LIKE '%$search%' OR district LIKE '%$search%' 
                ORDER BY CASE WHEN code LIKE '$search%' THEN 0 ELSE 1 END, code ASC 
                LIMIT 50";
    } elseif ($sector_id !== null && $sector_id > 0) {
        $sql = "SELECT id, code, name, district, district_id, sector, sector_id, area, fee AS delivery_fee 
                FROM kigali_streets 
                WHERE sector_id = $sector_id " . (!empty($sector_name) ? " OR sector LIKE '%$sector_name%'" : "") . " 
                ORDER BY code ASC";
    } elseif (!empty($sector_name)) {
        $sql = "SELECT id, code, name, district, district_id, sector, sector_id, area, fee AS delivery_fee 
                FROM kigali_streets 
                WHERE sector LIKE '%$sector_name%' OR area LIKE '%$sector_name%' 
                ORDER BY code ASC";
    } else {
        $sql = "SELECT id, code, name, district, district_id, sector, sector_id, area, fee AS delivery_fee 
                FROM kigali_streets 
                ORDER BY district ASC, sector ASC, code ASC";
    }

    $result = mysqli_query($conn, $sql);
    $streets = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $streets[] = [
                'id' => (int)$row['id'],
                'code' => $row['code'],
                'name' => $row['name'],
                'district' => $row['district'],
                'district_id' => (int)$row['district_id'],
                'sector' => $row['sector'],
                'sector_id' => (int)$row['sector_id'],
                'area' => $row['area'],
                'delivery_fee' => (float)$row['delivery_fee'],
                'type' => 'street'
            ];
        }
    }
    echo json_encode(['status' => 'success', 'data' => $streets]);
    exit;
}

// 2. Location Hierarchy Query (rwanda_locations)
if (!empty($search)) {
    // Global search across all types
    $sql = "SELECT id, name, type, delivery_fee FROM rwanda_locations WHERE name LIKE '%$search%' ORDER BY type ASC, name ASC LIMIT 20";
} else {
    if ($type === 'village') {
        $sql = "SELECT v.id, v.delivery_fee, 
                   CONCAT(v.name, ' (', c.name, ', ', s.name, ', ', p.name, ')') AS name
            FROM rwanda_locations v
            LEFT JOIN rwanda_locations c ON v.parent_id = c.id
            LEFT JOIN rwanda_locations s ON c.parent_id = s.id
            LEFT JOIN rwanda_locations d ON s.parent_id = d.id
            LEFT JOIN rwanda_locations p ON d.parent_id = p.id
            WHERE v.type = 'village'";
    } else {
        $sql = "SELECT id, name, delivery_fee FROM rwanda_locations WHERE type = '$type'";
    }
    
    if ($parent_id !== null) {
        $sql .= " AND " . ($type === 'village' ? "v." : "") . "parent_id = $parent_id";
    } else {
        $sql .= " AND " . ($type === 'village' ? "v." : "") . "parent_id IS NULL";
    }
    $sql .= " ORDER BY " . ($type === 'village' ? "v." : "") . "name ASC";
}

$lang = isset($_GET['lang']) ? clean_input($conn, $_GET['lang']) : ($_SESSION['lang'] ?? 'en');

$province_translations = [
    'rw' => [
        'KIGALI' => 'Umujyi wa Kigali',
        'SOUTH' => 'Intara y\'Amajyepfo',
        'WEST' => 'Intara y\'Iburengerazuba',
        'NORTH' => 'Intara y\'Amajyaruguru',
        'EAST' => 'Intara y\'Iburasirazuba',
    ],
    'en' => [
        'KIGALI' => 'Kigali City',
        'SOUTH' => 'Southern Province',
        'WEST' => 'Western Province',
        'NORTH' => 'Northern Province',
        'EAST' => 'Eastern Province',
    ]
];

$result = mysqli_query($conn, $sql);
$locations = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $displayName = $row['name'];
        if ($type === 'province') {
            $key = strtoupper(trim($row['name']));
            if (isset($province_translations[$lang][$key])) {
                $displayName = $province_translations[$lang][$key];
            } elseif (isset($province_translations['en'][$key])) {
                $displayName = $province_translations['en'][$key];
            }
        }
        $loc = [
            'id' => $row['id'],
            'name' => $displayName,
            'raw_name' => $row['name'],
            'delivery_fee' => (float)$row['delivery_fee']
        ];
        if (isset($row['type'])) {
            $loc['type'] = $row['type'];
        }
        $locations[] = $loc;
    }
}

echo json_encode(['status' => 'success', 'data' => $locations]);

