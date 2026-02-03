<?php
// Enable error reporting for debugging (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once 'functions.php';

$message = '';
$filterType = isset($_GET['type']) ? trim($_GET['type']) : 'all';
$filterDate = isset($_GET['date']) ? trim($_GET['date']) : '';
$filterPostcode = isset($_GET['postcode']) ? trim($_GET['postcode']) : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

// Sanitize search query to prevent issues with special characters
if (!empty($searchQuery)) {
    // Remove any potentially problematic characters but keep common ones
    $searchQuery = preg_replace('/[^\p{L}\p{N}\s\-_.,@#]/u', '', $searchQuery);
}

// Handle delete action
if ($action === 'delete' && isset($_GET['filename'])) {
    if (deleteRecord($_GET['filename'])) {
        $message = '<div class="alert alert-success">Record deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-error">Failed to delete record.</div>';
    }
}

// Get records based on filters
try {
    if ($filterType === 'all') {
        $records = getAllRecords();
    } else {
        $records = getRecordsByType($filterType);
    }
} catch (Exception $e) {
    $records = [];
    $message = '<div class="alert alert-error">Error loading records: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

if (!empty($filterDate)) {
    $records = array_filter($records, function($record) use ($filterDate) {
        if (!isset($record['created_at'])) return false;
        return date('Y-m-d', strtotime($record['created_at'])) === $filterDate;
    });
}

// Filter by postcode
if (!empty($filterPostcode)) {
    $records = array_filter($records, function($record) use ($filterPostcode) {
        if (!isset($record['data']) || !is_array($record['data'])) return false;
        
        $postcodeFields = [
            'site_postcode', 'landlord_postcode', 'business_postcode', // gas_safety
            'client_postcode', // invoice
        ];
        
        $filterPostcodeLower = mb_strtolower($filterPostcode, 'UTF-8');
        
        foreach ($postcodeFields as $field) {
            if (isset($record['data'][$field]) && is_string($record['data'][$field])) {
                $postcodeValue = mb_strtolower($record['data'][$field], 'UTF-8');
                if (mb_strpos($postcodeValue, $filterPostcodeLower, 0, 'UTF-8') !== false) {
                    return true;
                }
            }
        }
        return false;
    });
}

// Search filter (searches across multiple fields)
if (!empty($searchQuery)) {
    $records = array_filter($records, function($record) use ($searchQuery) {
        if (!isset($record['data']) || !is_array($record['data'])) return false;
        
        $searchFields = [
            // Gas Safety fields - updated for new textarea format
            'site_name', 'site_address', 'site_postcode',
            'landlord_name', 'landlord_address', 'landlord_postcode',
            'business_name', 'business_address', 'business_postcode',
            'serial_no', 'reference',
            // Legacy fields (backward compatibility)
            'site_address1', 'site_address2', 'site_address3', 'site_address4',
            'landlord_address1', 'landlord_address2', 'landlord_address3', 'landlord_address4',
            'business_address1', 'business_address2', 'business_address3',
            // Invoice fields
            'client_name', 'client_address', 'client_address1', 'client_postcode',
            'invoice_number',
            // Service checklist fields
            'site_tenant_name', 'appliance_serial', 'appliance_manufacturer'
        ];
        
        $searchQueryLower = mb_strtolower($searchQuery, 'UTF-8');
        
        foreach ($searchFields as $field) {
            if (isset($record['data'][$field])) {
                $fieldValue = $record['data'][$field];
                if (is_string($fieldValue)) {
                    $fieldValueLower = mb_strtolower($fieldValue, 'UTF-8');
                    if (mb_strpos($fieldValueLower, $searchQueryLower, 0, 'UTF-8') !== false) {
                        return true;
                    }
                }
            }
        }
        // Also search in filename
        if (isset($record['filename'])) {
            $filenameLower = mb_strtolower($record['filename'], 'UTF-8');
            if (mb_strpos($filenameLower, $searchQueryLower, 0, 'UTF-8') !== false) {
                return true;
            }
        }
        return false;
    });
}

// Get unique postcodes from all records
$uniquePostcodes = [];
try {
    $allRecordsForPostcodes = getAllRecords();
    foreach ($allRecordsForPostcodes as $rec) {
        if (!isset($rec['data']) || !is_array($rec['data'])) continue;
        
        $postcodeFields = ['site_postcode', 'landlord_postcode', 'business_postcode', 'client_postcode'];
        foreach ($postcodeFields as $field) {
            if (isset($rec['data'][$field]) && !empty($rec['data'][$field])) {
                $pc = trim($rec['data'][$field]);
                if (!empty($pc)) {
                    $uniquePostcodes[$pc] = true;
                }
            }
        }
    }
    ksort($uniquePostcodes);
    $uniquePostcodes = array_keys($uniquePostcodes);
} catch (Exception $e) {
    $uniquePostcodes = [];
}

// Get unique dates and record counts
$uniqueDates = getUniqueDates();
$recordCounts = [
    'gas_safety' => getRecordCountByType('gas_safety'),
    'invoice' => getRecordCountByType('invoice'),
    'service_checklist' => getRecordCountByType('service_checklist')
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Saved Records - Orient Gas Engineers</title>
<style>
  body {
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, Helvetica, sans-serif;
    margin: 0;
    padding: 20px;
    background-color: #f8f9fa;
    color: #333;
  }

  .container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
  }

  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
  }

  .header h1 {
    color: #2e5aa6;
    margin: 0;
    font-size: 2.2em;
  }

  .nav-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .btn {
    appearance: none;
    border: 1px solid #cad2e2;
    background: #fff;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
    font-size: 14px;
  }

  .btn:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
  }

  .btn.primary {
    background: #2e5aa6;
    color: #fff;
    border-color: #2e5aa6;
  }

  .btn.primary:hover {
    background: #1e4a96;
  }

  .btn.danger {
    background: #dc3545;
    color: #fff;
    border-color: #dc3545;
  }

  .btn.danger:hover {
    background: #c82333;
  }

  .btn.success {
    background: #28a745;
    color: #fff;
    border-color: #28a745;
  }

  .btn.info {
    background: #17a2b8;
    color: #fff;
    border-color: #17a2b8;
  }

  .btn.small {
    padding: 6px 10px;
    font-size: 12px;
  }

  .alert {
    padding: 15px;
    margin: 20px 0;
    border-radius: 8px;
    font-weight: bold;
  }

  .alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  .filters {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: center;
  }

  .filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }

  .filter-group label {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
  }

  .filter-group select,
  .filter-group input {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
  }

  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: linear-gradient(135deg, #2e5aa6, #1e4a96);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  }

  .stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 1.1em;
    opacity: 0.9;
  }

  .stat-card .number {
    font-size: 2.5em;
    font-weight: bold;
    margin: 0;
  }

  .records-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  .records-table th,
  .records-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
  }

  .records-table th {
    background: #2e5aa6;
    color: white;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .records-table tr:hover {
    background-color: #f8f9fa;
  }

  .record-type {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
  }

  .record-type.gas_safety {
    background: #e3f2fd;
    color: #1565c0;
  }

  .record-type.invoice {
    background: #e8f5e8;
    color: #2e7d32;
  }

  .record-type.service_checklist {
    background: #fff3e0;
    color: #ef6c00;
  }

  .actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
  }

  .empty-state h3 {
    margin: 0 0 10px 0;
    color: #495057;
  }

  .search-info {
    background: #e3f2fd;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    color: #1565c0;
    border-left: 4px solid #2196f3;
  }

  @media (max-width: 768px) {
    .container {
      padding: 20px;
      margin: 10px;
    }

    .header {
      flex-direction: column;
      gap: 15px;
      align-items: stretch;
    }

    .nav-buttons {
      justify-content: center;
    }

    .filters {
      grid-template-columns: 1fr;
    }

    .stats {
      grid-template-columns: 1fr;
    }

    .records-table {
      font-size: 14px;
    }

    .records-table th,
    .records-table td {
      padding: 8px 12px;
    }

    .actions {
      flex-direction: column;
    }
  }
</style>
</head>
<body>

<div class="container">
  <div class="header">
    <h1>Manage Saved Records</h1>
    <div class="nav-buttons">
      <a href="index.php" class="btn primary">Gas Safety Record</a>
      <a href="invoice.php" class="btn primary">Invoice</a>
      <a href="service_checklist.php" class="btn primary">Service Checklist</a>
    </div>
  </div>

  <?php if ($message): ?>
    <?= $message ?>
  <?php endif; ?>

  <!-- Statistics -->
  <div class="stats">
    <div class="stat-card">
      <h3>Gas Safety Records</h3>
      <div class="number"><?= $recordCounts['gas_safety'] ?></div>
    </div>
    <div class="stat-card">
      <h3>Invoices</h3>
      <div class="number"><?= $recordCounts['invoice'] ?></div>
    </div>
    <div class="stat-card">
      <h3>Service Checklists</h3>
      <div class="number"><?= $recordCounts['service_checklist'] ?></div>
    </div>
    <div class="stat-card">
      <h3>Total Records</h3>
      <div class="number"><?= array_sum($recordCounts) ?></div>
    </div>
  </div>

  <!-- Filters -->
  <form method="GET" action="" class="filters" accept-charset="UTF-8">
    <div class="filter-group">
      <label for="search">Search:</label>
      <input type="text" name="search" id="search" placeholder="Name, address, serial..." 
             value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>" maxlength="100">
    </div>

    <div class="filter-group">
      <label for="postcode">Filter by Postcode:</label>
      <select name="postcode" id="postcode">
        <option value="">All Postcodes</option>
        <?php foreach ($uniquePostcodes as $pc): ?>
          <option value="<?= htmlspecialchars($pc, ENT_QUOTES, 'UTF-8') ?>" <?= $filterPostcode === $pc ? 'selected' : '' ?>>
            <?= htmlspecialchars($pc, ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="filter-group">
      <label for="type">Filter by Type:</label>
      <select name="type" id="type">
        <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All Types</option>
        <option value="gas_safety" <?= $filterType === 'gas_safety' ? 'selected' : '' ?>>Gas Safety Records</option>
        <option value="invoice" <?= $filterType === 'invoice' ? 'selected' : '' ?>>Invoices</option>
        <option value="service_checklist" <?= $filterType === 'service_checklist' ? 'selected' : '' ?>>Service Checklists</option>
      </select>
    </div>

    <div class="filter-group">
      <label for="date">Filter by Date:</label>
      <select name="date" id="date">
        <option value="">All Dates</option>
        <?php foreach ($uniqueDates as $date): ?>
          <option value="<?= $date ?>" <?= $filterDate === $date ? 'selected' : '' ?>>
            <?= formatDate($date) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="filter-group">
      <label>&nbsp;</label>
      <button type="submit" class="btn info">Apply Filters</button>
    </div>

    <div class="filter-group">
      <label>&nbsp;</label>
      <a href="manage_data.php" class="btn">Clear Filters</a>
    </div>
  </form>

  <?php if ($filterType !== 'all' || !empty($filterDate) || !empty($filterPostcode) || !empty($searchQuery)): ?>
    <div class="search-info">
      <strong>Filtered Results:</strong>
      <?php if (!empty($searchQuery)): ?>
        Search: "<?= htmlspecialchars($searchQuery) ?>"
      <?php endif; ?>
      <?php if (!empty($filterPostcode)): ?>
        <?= !empty($searchQuery) ? ' | ' : '' ?>Postcode: <?= htmlspecialchars($filterPostcode) ?>
      <?php endif; ?>
      <?php if ($filterType !== 'all'): ?>
        <?= (!empty($searchQuery) || !empty($filterPostcode)) ? ' | ' : '' ?>Type: <?= ucfirst(str_replace('_', ' ', $filterType)) ?>
      <?php endif; ?>
      <?php if (!empty($filterDate)): ?>
        <?= ($filterType !== 'all' || !empty($searchQuery) || !empty($filterPostcode)) ? ' | ' : '' ?>Date: <?= formatDate($filterDate) ?>
      <?php endif; ?>
      (<?= count($records) ?> record<?= count($records) !== 1 ? 's' : '' ?> found)
    </div>
  <?php endif; ?>

  <!-- Records Table -->
  <?php if (empty($records)): ?>
    <div class="empty-state">
      <h3>No Records Found</h3>
      <p>No records match your current filters. Try adjusting your filters or create a new record.</p>
    </div>
  <?php else: ?>
    <table class="records-table">
      <thead>
        <tr>
          <th>Type</th>
          <th>Created</th>
          <th>Postcode</th>
          <th>Filename</th>
          <th>Preview</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($records as $record): ?>
          <tr>
            <td>
              <span class="record-type <?= $record['type'] ?>">
                <?= ucfirst(str_replace('_', ' ', $record['type'])) ?>
              </span>
            </td>
            <td>
              <div style="font-weight: 600;"><?= formatDate($record['created_at'], 'M j, Y') ?></div>
              <div style="font-size: 12px; color: #6c757d;">
                <?= date('g:i A', strtotime($record['created_at'])) ?>
              </div>
            </td>
            <td>
              <?php 
              $postcode = '';
              if ($record['type'] === 'gas_safety') {
                $postcode = $record['data']['site_postcode'] ?? $record['data']['landlord_postcode'] ?? '';
              } elseif ($record['type'] === 'invoice') {
                $postcode = $record['data']['client_postcode'] ?? '';
              } elseif ($record['type'] === 'service_checklist') {
                $postcode = $record['data']['site_postcode'] ?? '';
              }
              ?>
              <span style="font-weight: 500;"><?= htmlspecialchars($postcode) ?></span>
            </td>
            <td>
              <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                <?= htmlspecialchars($record['filename']) ?>
              </code>
            </td>
            <td>
              <?php 
              $previewData = '';
              if ($record['type'] === 'gas_safety') {
                $previewData = $record['data']['site_name'] ?? $record['data']['landlord_name'] ?? 'Gas Safety Record';
              } elseif ($record['type'] === 'invoice') {
                $previewData = ($record['data']['invoice_number'] ?? 'Invoice') . ' - ' . ($record['data']['client_name'] ?? 'Client');
              } elseif ($record['type'] === 'service_checklist') {
                $previewData = $record['data']['site_tenant_name'] ?? $record['data']['appliance_location'] ?? 'Service Checklist';
              }
              ?>
              <div style="font-weight: 500; color: #495057;">
                <?= htmlspecialchars($previewData) ?>
              </div>
              <?php if (isset($record['modified_at'])): ?>
                <div style="font-size: 11px; color: #6c757d;">
                  Modified: <?= date('M j, Y g:i A', strtotime($record['modified_at'])) ?>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <div class="actions">
                <a href="view_record.php?file=<?= urlencode($record['filename']) ?>" 
                   class="btn small info">View</a>
                <?php
                $editPage = 'index.php';
                if ($record['type'] === 'invoice') {
                  $editPage = 'invoice.php';
                } elseif ($record['type'] === 'service_checklist') {
                  $editPage = 'service_checklist.php';
                }
                ?>
                <a href="<?= $editPage ?>?edit=<?= urlencode($record['filename']) ?>" 
                   class="btn small success">Edit</a>
                <a href="?action=delete&filename=<?= urlencode($record['filename']) ?>&type=<?= urlencode($filterType) ?>&date=<?= urlencode($filterDate) ?>&postcode=<?= urlencode($filterPostcode) ?>&search=<?= urlencode($searchQuery) ?>" 
                   class="btn small danger"
                   onclick="return confirm('Are you sure you want to delete this record? This action cannot be undone.')">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script>
// Auto-submit form when filters change
document.getElementById('type').addEventListener('change', function() {
  this.form.submit();
});

document.getElementById('date').addEventListener('change', function() {
  this.form.submit();
});

document.getElementById('postcode').addEventListener('change', function() {
  this.form.submit();
});

// Submit search on Enter key
document.getElementById('search').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
    this.form.submit();
  }
});
</script>

</body>
</html>