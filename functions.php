<?php
// Shared PHP functions for Orient Gas Engineers application

/**
 * Get the data directory path
 */
function getDataDirectory() {
    return __DIR__ . '/data';
}

/**
 * Ensure the data directory exists
 */
function ensureDataDirectory() {
    $dataDir = getDataDirectory();
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    return $dataDir;
}

/**
 * Generate a unique filename based on type and date
 */
function generateFileName($type, $date = null) {
    if (!$date) {
        $date = date('Y-m-d');
    }
    $timestamp = date('H-i-s');
    return $type . '_' . $date . '_' . $timestamp . '.json';
}

/**
 * Save form data to JSON file
 */
function saveFormData($type, $data) {
    $dataDir = ensureDataDirectory();
    $filename = generateFileName($type);
    $filepath = $dataDir . '/' . $filename;
    
    // Add metadata
    $record = [
        'type' => $type,
        'created_at' => date('Y-m-d H:i:s'),
        'filename' => $filename,
        'data' => $data
    ];
    
    $jsonData = json_encode($record, JSON_PRETTY_PRINT);
    
    if (file_put_contents($filepath, $jsonData)) {
        return $filename;
    }
    
    return false;
}

/**
 * Load form data from JSON file
 */
function loadFormData($filename) {
    $dataDir = getDataDirectory();
    $filepath = $dataDir . '/' . $filename;
    
    if (!file_exists($filepath)) {
        return false;
    }
    
    $jsonData = file_get_contents($filepath);
    $record = json_decode($jsonData, true);
    
    return $record;
}

/**
 * Get all saved records
 */
function getAllRecords() {
    $dataDir = getDataDirectory();
    $records = [];
    
    if (is_dir($dataDir)) {
        $files = glob($dataDir . '/*.json');
        
        foreach ($files as $file) {
            $filename = basename($file);
            $record = loadFormData($filename);
            if ($record) {
                $records[] = $record;
            }
        }
        
        // Sort by creation date (newest first)
        usort($records, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
    }
    
    return $records;
}

/**
 * Get records by type
 */
function getRecordsByType($type) {
    $allRecords = getAllRecords();
    return array_filter($allRecords, function($record) use ($type) {
        return $record['type'] === $type;
    });
}

/**
 * Get records by date
 */
function getRecordsByDate($date) {
    $allRecords = getAllRecords();
    return array_filter($allRecords, function($record) use ($date) {
        return date('Y-m-d', strtotime($record['created_at'])) === $date;
    });
}

/**
 * Delete a record
 */
function deleteRecord($filename) {
    $dataDir = getDataDirectory();
    $filepath = $dataDir . '/' . $filename;
    
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    
    return false;
}

/**
 * Update existing record
 */
function updateFormData($filename, $data) {
    $record = loadFormData($filename);
    if (!$record) {
        return false;
    }
    
    // Update the data and modified timestamp
    $record['data'] = $data;
    $record['modified_at'] = date('Y-m-d H:i:s');
    
    $dataDir = getDataDirectory();
    $filepath = $dataDir . '/' . $filename;
    $jsonData = json_encode($record, JSON_PRETTY_PRINT);
    
    return file_put_contents($filepath, $jsonData) !== false;
}

/**
 * Sanitize form input
 * Note: Only trim input here, HTML encoding should happen at display time
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return trim($input);
}

/**
 * Get unique dates from all records
 */
function getUniqueDates() {
    $allRecords = getAllRecords();
    $dates = [];
    
    foreach ($allRecords as $record) {
        $date = date('Y-m-d', strtotime($record['created_at']));
        $dates[$date] = true;
    }
    
    $uniqueDates = array_keys($dates);
    rsort($uniqueDates); // Sort newest first
    
    return $uniqueDates;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Get record count by type
 */
function getRecordCountByType($type) {
    return count(getRecordsByType($type));
}

/**
 * Generate auto-incrementing serial number for gas safety records
 * Format: ORIENTGAUK + 8 digit padded number (e.g., ORIENTGAUK00000001)
 */
function generateSerialNumber($type = 'gas_safety') {
    $records = getRecordsByType($type);
    $maxSerial = 0; // Starting from 0
    
    $fieldName = ($type === 'service_checklist') ? 'appliance_serial' : 'serial_no';
    
    foreach ($records as $record) {
        if (isset($record['data'][$fieldName])) {
            $serialNo = $record['data'][$fieldName];
            // Extract numeric part from ORIENTGAUK prefix
            if (preg_match('/^ORIENTGAUK(\d+)$/', $serialNo, $matches)) {
                $num = intval($matches[1]);
                if ($num > $maxSerial) {
                    $maxSerial = $num;
                }
            }
        }
    }
    
    // Increment and format with ORIENTGAUK prefix and 8-digit padding
    $nextSerial = $maxSerial + 1;
    return 'ORIENTGAUK' . str_pad($nextSerial, 8, '0', STR_PAD_LEFT);
}

/**
 * Generate auto-incrementing invoice number
 * Format: 5 digit padded number (e.g., 00001, 00022)
 */
function generateInvoiceNumber() {
    $records = getRecordsByType('invoice');
    $maxNumber = 0;
    
    foreach ($records as $record) {
        if (isset($record['data']['invoice_number'])) {
            $invoiceNo = $record['data']['invoice_number'];
            // Extract numeric part
            if (preg_match('/^(\d+)$/', $invoiceNo, $matches)) {
                $num = intval($matches[1]);
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }
    }
    
    // Increment and format with 5-digit padding
    $nextNumber = $maxNumber + 1;
    return str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
}
?>