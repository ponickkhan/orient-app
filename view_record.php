<?php
require_once 'functions.php';

$filename = $_GET['file'] ?? '';
$record = null;
$error = '';

if (empty($filename)) {
    $error = 'No filename specified.';
} else {
    $record = loadFormData($filename);
    if (!$record) {
        $error = 'Record not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>View Record - <?= $filename ?></title>
<style>
  body {
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, Helvetica, sans-serif;
    margin: 0;
    padding: 20px;
    background-color: #f8f9fa;
    color: #333;
    line-height: 1.6;
  }

  .container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
  }

  .header {
    background: linear-gradient(135deg, #2e5aa6, #1e4a96);
    color: white;
    padding: 30px;
    text-align: center;
  }

  .header h1 {
    margin: 0 0 10px 0;
    font-size: 2.2em;
  }

  .header .meta {
    opacity: 0.9;
    font-size: 1.1em;
  }

  .nav-buttons {
    padding: 20px 30px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
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

  .content {
    padding: 30px;
  }

  .record-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
  }

  .info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }

  .info-label {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
  }

  .info-value {
    color: #212529;
    font-weight: 500;
  }

  .record-type {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
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

  .data-section {
    margin-bottom: 30px;
  }

  .section-title {
    color: #2e5aa6;
    font-size: 1.3em;
    font-weight: 600;
    margin: 0 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
  }

  .data-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
  }

  .data-group {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #2e5aa6;
  }

  .data-group h4 {
    margin: 0 0 15px 0;
    color: #495057;
    font-size: 1.1em;
  }

  .data-item {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 10px;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
  }

  .data-item:last-child {
    margin-bottom: 0;
    border-bottom: none;
  }

  .data-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 14px;
  }

  .data-value {
    color: #212529;
    font-weight: 500;
    word-break: break-word;
  }

  .empty-value {
    color: #6c757d;
    font-style: italic;
  }

  .json-viewer {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
  }

  .json-content {
    background: #2d3748;
    color: #e2e8f0;
    padding: 15px;
    border-radius: 6px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 13px;
    line-height: 1.5;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-all;
  }

  .error {
    background: #f8d7da;
    color: #721c24;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #f5c6cb;
    text-align: center;
    margin: 20px;
  }

  .toggle-json {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    margin-top: 10px;
  }

  @media (max-width: 768px) {
    .container {
      margin: 10px;
    }

    .nav-buttons {
      flex-direction: column;
    }

    .data-grid {
      grid-template-columns: 1fr;
    }

    .data-item {
      grid-template-columns: 1fr;
      gap: 5px;
    }

    .record-info {
      grid-template-columns: 1fr;
    }
  }
</style>
</head>
<body>

<div class="container">
  <?php if ($error): ?>
    <div class="error">
      <h2>Error</h2>
      <p><?= htmlspecialchars($error) ?></p>
      <a href="manage_data.php" class="btn primary">Back to Records</a>
    </div>
  <?php else: ?>
    <div class="header">
      <h1>Record Details</h1>
      <div class="meta">
        <span class="record-type <?= $record['type'] ?>">
          <?= ucfirst(str_replace('_', ' ', $record['type'])) ?>
        </span>
      </div>
    </div>

    <div class="nav-buttons">
      <a href="manage_data.php" class="btn primary">← Back to Records</a>
      <?php
      $editPage = 'index.php';
      if ($record['type'] === 'invoice') {
        $editPage = 'invoice.php';
      } elseif ($record['type'] === 'service_checklist') {
        $editPage = 'service_checklist.php';
      }
      ?>
      <a href="<?= $editPage ?>?edit=<?= urlencode($filename) ?>" class="btn success">Edit Record</a>
      <a href="javascript:window.print()" class="btn info">Print Record</a>
    </div>

    <div class="content">
      <!-- Record Information -->
      <div class="record-info">
        <div class="info-item">
          <div class="info-label">Filename</div>
          <div class="info-value">
            <code><?= htmlspecialchars($filename) ?></code>
          </div>
        </div>
        <div class="info-item">
          <div class="info-label">Type</div>
          <div class="info-value">
            <span class="record-type <?= $record['type'] ?>">
              <?= ucfirst(str_replace('_', ' ', $record['type'])) ?>
            </span>
          </div>
        </div>
        <div class="info-item">
          <div class="info-label">Created</div>
          <div class="info-value"><?= formatDate($record['created_at'], 'F j, Y \a\t g:i A') ?></div>
        </div>
        <?php if (isset($record['modified_at'])): ?>
        <div class="info-item">
          <div class="info-label">Last Modified</div>
          <div class="info-value"><?= formatDate($record['modified_at'], 'F j, Y \a\t g:i A') ?></div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Record Data -->
      <?php if ($record['type'] === 'gas_safety'): ?>
        <?php include 'view_templates/gas_safety.php'; ?>
      <?php elseif ($record['type'] === 'invoice'): ?>
        <?php include 'view_templates/invoice.php'; ?>
      <?php elseif ($record['type'] === 'service_checklist'): ?>
        <?php include 'view_templates/service_checklist.php'; ?>
      <?php endif; ?>

      <!-- Raw JSON Data (collapsible) -->
      <div class="json-viewer">
        <button class="toggle-json" onclick="toggleJson()">Toggle Raw JSON Data</button>
        <div id="jsonContent" class="json-content" style="display: none;">
          <?= htmlspecialchars(json_encode($record, JSON_PRETTY_PRINT)) ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
function toggleJson() {
  const content = document.getElementById('jsonContent');
  content.style.display = content.style.display === 'none' ? 'block' : 'none';
}
</script>

</body>
</html>