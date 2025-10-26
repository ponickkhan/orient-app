<?php
require_once 'functions.php';

$message = '';
$formData = [];
$editMode = false;
$editFilename = '';

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save') {
            $data = sanitizeInput($_POST);
            unset($data['action']); // Remove action from saved data
            
            if (isset($_POST['edit_filename']) && !empty($_POST['edit_filename'])) {
                // Update existing record
                if (updateFormData($_POST['edit_filename'], $data)) {
                    $message = '<div class="alert alert-success">Invoice updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-error">Failed to update invoice.</div>';
                }
            } else {
                // Save new record
                $filename = saveFormData('invoice', $data);
                if ($filename) {
                    $message = '<div class="alert alert-success">Invoice saved successfully! Filename: ' . $filename . '</div>';
                } else {
                    $message = '<div class="alert alert-error">Failed to save invoice.</div>';
                }
            }
        }
    }
}

// Handle edit mode
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $record = loadFormData($_GET['edit']);
    if ($record) {
        $formData = $record['data'];
        $editMode = true;
        $editFilename = $_GET['edit'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>ORIENT Gas Engineers LTD — Invoice Generator</title>
<style>
  /* ===== Page & Base ===== */
  @page { size: A4 portrait; margin: 12mm; }
  :root{
    --ink:#1b1e24;
    --blue:#2e5aa6;
    --line:#cfd6e6;
    --line-dark:#2e5aa6;
    --muted:#6c7280;
    --bg:#ffffff;
    --chip:#eef3ff;
  }
  html, body { height:100%; }
  body {
    margin:0;
    color:var(--ink);
    background:var(--bg);
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, Helvetica, sans-serif;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
  }
  .wrap { max-width: 900px; margin: 24px auto; padding: 0 16px 40px; }

  /* ===== Alerts ===== */
  .alert {
    padding: 12px;
    margin: 10px 0;
    border-radius: 5px;
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
  .alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
  }

  /* ===== Toolbar (not printed) ===== */
  .toolbar {
    position: sticky; top:0; z-index:50;
    background:#f7f9ff; border-bottom:1px solid var(--line);
    display:flex; gap:8px; padding:10px; justify-content:flex-end;
  }
  .btn {
    appearance:none; border:1px solid var(--line); background:#fff;
    padding:10px 14px; border-radius:10px; font-weight:700; cursor:pointer; text-decoration: none; display: inline-block;
  }
  .btn.primary { background:var(--blue); color:#fff; border-color:var(--blue); }
  .btn.success { background:#28a745; color:#fff; border-color:#28a745; }
  .btn.info { background:#17a2b8; color:#fff; border-color:#17a2b8; }
  .btn.danger { color:#b00020; border-color:#f1c7cd; background:#fff; }
  .btn.small { padding:6px 10px; border-radius:8px; font-size:13px; }

  /* ===== Invoice Canvas ===== */
  .sheet { background:#fff; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
  .header {
    display:grid; grid-template-columns: 1.2fr 1fr; gap:16px;
    padding:20px 20px 0;
    align-items: start;
  }
  .brand { display:flex; align-items:flex-start; gap:14px; }
  .logo { width:70px; height:70px; border-radius:10px; object-fit:cover; }
  .brand-info h1 { margin:0 0 4px; font-size:22px; color:var(--blue); }
  .brand-info p { margin:2px 0; color:var(--muted); font-size:14px; }

  .invoice-meta { text-align:right; }
  .invoice-title { font-size:28px; font-weight:800; color:var(--blue); margin:0 0 8px; }
  .meta-row { display:flex; justify-content:space-between; align-items:center; margin:4px 0; }
  .meta-label { font-weight:600; color:var(--muted); font-size:14px; }
  .meta-value { font-weight:700; color:var(--ink); }

  /* ===== Billing Section ===== */
  .billing {
    display:grid; grid-template-columns: 1fr 1fr; gap:20px;
    padding:20px; border-bottom:1px solid var(--line);
  }
  .billing-block h3 { margin:0 0 10px; font-size:16px; color:var(--blue); }
  .billing-block p { margin:2px 0; font-size:14px; color:var(--ink); }

  /* ===== Items Table ===== */
  .items { padding:0; }
  .items-table { width:100%; border-collapse:collapse; }
  .items-table th { 
    background:var(--chip); padding:12px 16px; text-align:left; 
    font-weight:600; color:var(--blue); border-bottom:2px solid var(--line);
  }
  .items-table td { 
    padding:12px 16px; border-bottom:1px solid var(--line);
    vertical-align:top;
  }
  .items-table input, .items-table textarea {
    width:100%; border:none; background:transparent; font:inherit;
  }
  .qty-input, .rate-input, .amount-display { text-align:right; }

  /* ===== Totals ===== */
  .totals {
    padding:20px; border-top:1px solid var(--line);
    display:flex; justify-content:flex-end;
  }
  .totals-table { width:300px; }
  .totals-table td { padding:8px 16px; }
  .totals-table .label { text-align:right; font-weight:600; color:var(--muted); }
  .totals-table .value { text-align:right; font-weight:700; }
  .totals-table .total-row { border-top:2px solid var(--line); }
  .totals-table .total-row .value { color:var(--blue); font-size:18px; }

  /* ===== Footer ===== */
  .footer {
    padding:20px; background:var(--chip); border-top:1px solid var(--line);
    text-align:center; color:var(--muted); font-size:13px;
  }

  /* ===== Print Styles ===== */
  @media print {
    .toolbar, .alert { display:none !important; }
    .wrap { margin:0; max-width:none; padding:0; }
    .sheet { border:none; border-radius:0; }
  }

  /* ===== Form Styles ===== */
  input[type="text"], input[type="date"], input[type="email"], input[type="number"], textarea {
    border:none; background:transparent; font:inherit; color:inherit; width:100%;
  }
  input:focus, textarea:focus { outline:1px solid var(--blue); }
  .add-item-btn, .remove-item-btn {
    appearance:none; border:1px solid var(--line); background:#fff;
    padding:6px 10px; border-radius:6px; cursor:pointer; font-size:12px;
  }
  .add-item-btn { color:var(--blue); }
  .remove-item-btn { color:#dc3545; }
</style>
</head>
<body>

<div class="toolbar">
  <button class="btn" type="button" onclick="document.querySelector('form').reset(); calculateTotals();">Reset</button>
  <button class="btn success" type="button" onclick="document.querySelector('form').submit();">Save Invoice</button>
  <a href="manage_data.php" class="btn info">View Saved Records</a>
  <button class="btn primary" type="button" onclick="window.print()">Print / Save PDF</button>
</div>

<div class="wrap">
  <?php if ($message): ?>
    <?= $message ?>
  <?php endif; ?>
  
  <div style="display:flex; gap:16px; margin-bottom:24px; justify-content:center;">
    <a href="index.php" class="btn">Home</a>
    <a href="invoice.php" class="btn primary">Invoice</a>
    <a href="service_checklist.php" class="btn">Service &amp; Maintenance Checklist</a>
  </div>

  <div class="sheet">
    <form method="POST" action="">
      <input type="hidden" name="action" value="save">
      <?php if ($editMode): ?>
        <input type="hidden" name="edit_filename" value="<?= htmlspecialchars($editFilename) ?>">
        <div class="alert alert-info">Editing existing invoice: <?= htmlspecialchars($editFilename) ?></div>
      <?php endif; ?>
      
      <!-- Header -->
      <div class="header">
        <div class="brand">
          <img class="logo" src="logo.png" alt="Company Logo" />
          <div class="brand-info">
            <h1>ORIENT Gas Engineers LTD</h1>
            <p>Gas Safe Registered: <strong>927879</strong></p>
            <p>45 Chalk Pit Avenue, Orpington, Kent BR5 3JJ</p>
            <p>Phone: +44 7795 999196</p>
            <p>Email: info@orientgas.co.uk</p>
          </div>
        </div>
        <div class="invoice-meta">
          <h2 class="invoice-title">INVOICE</h2>
          <div class="meta-row">
            <span class="meta-label">Invoice #:</span>
            <input class="meta-value" type="text" name="invoice_number" value="<?= htmlspecialchars($formData['invoice_number'] ?? 'INV-' . date('Ymd') . '-001') ?>" style="width:140px;">
          </div>
          <div class="meta-row">
            <span class="meta-label">Date:</span>
            <input class="meta-value" type="date" name="invoice_date" value="<?= htmlspecialchars($formData['invoice_date'] ?? date('Y-m-d')) ?>" style="width:140px;">
          </div>
          <div class="meta-row">
            <span class="meta-label">Due Date:</span>
            <input class="meta-value" type="date" name="due_date" value="<?= htmlspecialchars($formData['due_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>" style="width:140px;">
          </div>
        </div>
      </div>

      <!-- Billing Information -->
      <div class="billing">
        <div class="billing-block">
          <h3>Bill To:</h3>
          <input type="text" name="client_name" value="<?= htmlspecialchars($formData['client_name'] ?? '') ?>" placeholder="Client Name" style="font-weight:700; font-size:16px; margin-bottom:6px;">
          <br>
          <input type="text" name="client_address1" value="<?= htmlspecialchars($formData['client_address1'] ?? '') ?>" placeholder="Address Line 1">
          <br>
          <input type="text" name="client_address2" value="<?= htmlspecialchars($formData['client_address2'] ?? '') ?>" placeholder="Address Line 2">
          <br>
          <input type="text" name="client_city" value="<?= htmlspecialchars($formData['client_city'] ?? '') ?>" placeholder="City">
          <br>
          <input type="text" name="client_postcode" value="<?= htmlspecialchars($formData['client_postcode'] ?? '') ?>" placeholder="Postcode">
        </div>
        <div class="billing-block">
          <h3>Service Address:</h3>
          <input type="text" name="service_address1" value="<?= htmlspecialchars($formData['service_address1'] ?? '') ?>" placeholder="Service Address Line 1" style="font-weight:700; margin-bottom:6px;">
          <br>
          <input type="text" name="service_address2" value="<?= htmlspecialchars($formData['service_address2'] ?? '') ?>" placeholder="Service Address Line 2">
          <br>
          <input type="text" name="service_city" value="<?= htmlspecialchars($formData['service_city'] ?? '') ?>" placeholder="City">
          <br>
          <input type="text" name="service_postcode" value="<?= htmlspecialchars($formData['service_postcode'] ?? '') ?>" placeholder="Postcode">
        </div>
      </div>

      <!-- Items Table -->
      <div class="items">
        <table class="items-table" id="itemsTable">
          <thead>
            <tr>
              <th style="width:45%">Description</th>
              <th style="width:10%">Qty</th>
              <th style="width:15%">Rate</th>
              <th style="width:15%">Amount</th>
              <th style="width:15%">Action</th>
            </tr>
          </thead>
          <tbody id="itemsTableBody">
            <!-- Items will be dynamically added here -->
          </tbody>
        </table>
        
        <div style="padding:16px; border-bottom:1px solid var(--line);">
          <button type="button" class="add-item-btn" onclick="addItem()">+ Add Item</button>
        </div>
      </div>

      <!-- Totals -->
      <div class="totals">
        <table class="totals-table">
          <tr>
            <td class="label">Subtotal:</td>
            <td class="value">£<span id="subtotal">0.00</span></td>
          </tr>
          <tr>
            <td class="label">VAT (20%):</td>
            <td class="value">£<span id="vat">0.00</span></td>
          </tr>
          <tr class="total-row">
            <td class="label">Total:</td>
            <td class="value">£<span id="total">0.00</span></td>
          </tr>
        </table>
      </div>

      <!-- Footer -->
      <div class="footer">
        <p><strong>Payment Terms:</strong> Net 30 days</p>
        <p><strong>Payment Methods:</strong> Bank Transfer, Cash, Card</p>
        <p>Thank you for choosing ORIENT Gas Engineers LTD!</p>
      </div>
    </form>
  </div>
</div>

<script>
let itemCount = 0;

function addItem() {
  itemCount++;
  const tbody = document.getElementById('itemsTableBody');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td><textarea name="item_description[]" placeholder="Enter service description" rows="2" style="resize:vertical;"></textarea></td>
    <td><input type="number" name="item_quantity[]" value="1" min="0" step="0.01" class="qty-input" onchange="calculateRowTotal(this)"></td>
    <td><input type="number" name="item_rate[]" value="0.00" min="0" step="0.01" class="rate-input" onchange="calculateRowTotal(this)"></td>
    <td><span class="amount-display">£0.00</span></td>
    <td><button type="button" class="remove-item-btn" onclick="removeItem(this)">Remove</button></td>
  `;
  tbody.appendChild(row);
}

function removeItem(btn) {
  btn.closest('tr').remove();
  calculateTotals();
}

function calculateRowTotal(input) {
  const row = input.closest('tr');
  const qty = parseFloat(row.querySelector('[name="item_quantity[]"]').value) || 0;
  const rate = parseFloat(row.querySelector('[name="item_rate[]"]').value) || 0;
  const amount = qty * rate;
  row.querySelector('.amount-display').textContent = '£' + amount.toFixed(2);
  calculateTotals();
}

function calculateTotals() {
  const rows = document.querySelectorAll('#itemsTableBody tr');
  let subtotal = 0;
  
  rows.forEach(row => {
    const qty = parseFloat(row.querySelector('[name="item_quantity[]"]').value) || 0;
    const rate = parseFloat(row.querySelector('[name="item_rate[]"]').value) || 0;
    subtotal += qty * rate;
  });
  
  const vat = subtotal * 0.20;
  const total = subtotal + vat;
  
  document.getElementById('subtotal').textContent = subtotal.toFixed(2);
  document.getElementById('vat').textContent = vat.toFixed(2);
  document.getElementById('total').textContent = total.toFixed(2);
}

// Initialize with one item
addItem();

// Load existing items if in edit mode
<?php if ($editMode && !empty($formData['item_description'])): ?>
// Clear the initial item first
document.getElementById('itemsTableBody').innerHTML = '';

<?php 
$items = is_array($formData['item_description']) ? $formData['item_description'] : [$formData['item_description']];
for ($i = 0; $i < count($items); $i++): 
?>
addItem();
const row<?= $i ?> = document.querySelector('#itemsTableBody tr:last-child');
row<?= $i ?>.querySelector('[name="item_description[]"]').value = <?= json_encode($formData['item_description'][$i] ?? '') ?>;
row<?= $i ?>.querySelector('[name="item_quantity[]"]').value = <?= json_encode($formData['item_quantity'][$i] ?? '1') ?>;
row<?= $i ?>.querySelector('[name="item_rate[]"]').value = <?= json_encode($formData['item_rate'][$i] ?? '0.00') ?>;
calculateRowTotal(row<?= $i ?>.querySelector('[name="item_quantity[]"]'));
<?php endfor; ?>
<?php endif; ?>

calculateTotals();
</script>

</body>
</html>